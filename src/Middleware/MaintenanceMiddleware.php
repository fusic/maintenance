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

        'templateFileName' => 'maintenance',

        'contentType' => 'text/html',

        'useXForwardedFor' => false,
    ];

    public function __construct($config = [])
    {
        $this->setConfig($config);
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
        $cakeRequest = \Cake\Http\ServerRequestFactory::fromGlobals();
        $builder = new ViewBuilder();

        $className = $this->getConfig('className');
        $templateName = $this->getConfig('templateFileName');
        $templatePath = $this->getConfig('templatePath');
        $contentType = $this->getConfig('contentType');
        $statusCode = $this->getConfig('statusCode');

        $view = $builder
            ->setClassName($className)
            ->setTemplatePath(Inflector::camelize($templatePath))
            ->setLayout(false)
            ->disableAutoLayout()
            ->build([], $cakeRequest);
        $bodyString = $view->render($templateName);

        $response = $response
            ->withHeader('Content-Type', $contentType)
            ->withStatus($statusCode);
        $response
            ->getBody()
            ->write($bodyString);

        return $response;
    }

    /**
     * @return bool
     * @author gorogoroyasu
     */
    private function checkStatusFile()
    {
        $path = $this->getConfig('statusFilePath');
        if (is_string($path)) {
            $path = [$path];
        }
        foreach($path as $p) {
            $fullPath = $p . $this->getConfig('statusFileName');
            $ret = file_exists($fullPath);
            if ($ret === true) {
                return true;
            }
        }
        
        return false;
    }


    private function isMaintenance($request)
    {
        $ret = $this->checkStatusFile();
        if ($ret === false) {
            return false;
        }

        $ret = $this->isAllowIp($request);
        if ($ret === true) {
            return false;
        }

        return true;
    }

    private function getMyIpAddress($request)
    {
        $params = $request->getServerParams();

        // X-Forwarded-Forはカンマ区切り。一番近いReverse proxyで付与されたIPを末尾から取得する。
        if ($this->getConfig('useXForwardedFor') && isset($params['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $params['HTTP_X_FORWARDED_FOR']);
            return trim(array_pop($ips));
        } else {
            return isset($params['REMOTE_ADDR']) ? $params['REMOTE_ADDR'] : null;
        }
    }

    private function isAllowIp($request)
    {
        $myIpAddress = $this->getMyIpAddress($request);
        if (is_null($myIpAddress)) {
            return false;
        }

        $ipAddressList = $this->getConfig('allowIp');
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
