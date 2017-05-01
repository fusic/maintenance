<?php
namespace Maintenance\Middleware;

use Cake\Core\InstanceConfigTrait;
use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;
use Cake\Network\Request;

class MaintenanceMiddleware
{
    use InstanceConfigTrait;

    /**
     * Default config.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'allowIp' => [],

        'className' => 'Cake\View\View',
        'templatePath' => 'Error',

        'statusFilePath' => TMP,
        'statusFileName' => 'maintenance',
        'statusCode' => 503,

        'ctpFileName' => 'maintenance',
        'ctpExtension' => '.ctp',

        'contentType' => 'text/html'
    ];

    public function __construct($config = [])
    {
        $this->config($config);
    }

    public function __invoke($request, $response, $next)
    {
        $isActive = $this->isMaintenance($request);

        if ($isActive === false) {
            $response = $next($request, $response);
        } else {
            $response = $this->execute($response);
        }

        return $response;
    }

    private function execute($response)
    {
        $cakeRequest = Request::createFromGlobals();
        $builder = new ViewBuilder();

        $className = $this->config('className');
        $templateName = $this->config('ctpFileName');
        $templatePath = $this->config('templatePath');
        $ext = $this->config('ctpExtension');
        $contentType = $this->config('contentType');
        $statusCode = $this->config('statusCode');

        $view = $builder
            ->className($className)
            ->templatePath(Inflector::camelize($templatePath))
            ->layout(false)
            ->build([], $cakeRequest);
        $view->_ext = $ext;
        $bodyString = $view->render($templateName);

        $response = $response
            ->withHeader('Content-Type', $contentType)
            ->withStatus($statusCode);
        $response
            ->getBody()
            ->write($bodyString);

        return $response;
    }

    private function isMaintenance($request)
    {
        $fullPath = $this->config('statusFilePath') . $this->config('statusFileName');
        $ret = file_exists($fullPath);
        if ($ret === false) {
            return false;
        }

        $ret = $this->isAllowIp($request);
        if ($ret === true) {
            return false;
        }

        return true;
    }

    private function isAllowIp($request)
    {
        $params = $request->getServerParams();
        $myIpAddress = $params['REMOTE_ADDR'];

        $ipAddressList = $this->config('allowIp');
        if (empty($ipAddressList)) {
            return false;
        }

        foreach ($ipAddressList as $allowIP) {
            // サブネットマスクが指定されていない場合 /32 を追加
            if (strpos($allowIP, '/') == 0) {
                $allowIP .= '/32';
            }
            // IPアドレスの書式チェック
            if (!preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\/([1-9]|1[0-9]|2[0-9]|3[0-2])$/', $allowIP)) {
                // 書式が不正
                continue;
            }
            list($ip, $maskBit) = explode("/", $allowIP);
            $ipLong = ip2long($ip) >> (32 - $maskBit);

            $selfIpLong = ip2long($myIpAddress) >> (32 - $maskBit);
            if ($selfIpLong === $ipLong) {
                return true;
            }
        }

        return false;
    }
}
