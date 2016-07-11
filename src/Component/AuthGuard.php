<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 11:11
 */

namespace Tusimo\Wechat\Component;



use EasyWeChat\Core\Exceptions\FaultException;
use EasyWeChat\Core\Exceptions\InvalidArgumentException;
use EasyWeChat\Core\Exceptions\RuntimeException;
use EasyWeChat\Encryption\Encryptor;
use EasyWeChat\Server\BadRequestException;
use EasyWeChat\Support\Collection;
use EasyWeChat\Support\Log;
use EasyWeChat\Support\XML;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthGuard
{
    /**
     * Empty string.
     */
    const SUCCESS_EMPTY_RESPONSE = 'success';

    const COMPONENT_VERIFY_TICKET = 2;
    const AUTHORIZED = 4;
    const UNAUTHORIZED = 8;
    const UPDATEAUTHIRIZED = 16;
    const ALL_MSG = 1048830;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var string|callable
     */
    protected $messageHandler;

    /**
     * @var int
     */
    protected $messageFilter;

    /**
     * @var array
     */
    protected $messageTypeMapping = [
        'component_verify_ticket' => 2,
        'authoirzed' => 4,
        'unauthorized' => 8,
        'updateauthoirzed' => 16,
    ];

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Constructor.
     *
     * @param string  $token
     * @param Request $request
     */
    public function __construct($token, Request $request = null)
    {
        $this->token = $token;
        $this->request = $request ?: Request::createFromGlobals();
    }

    /**
     * Enable/Disable debug mode.
     *
     * @param bool $debug
     *
     * @return $this
     */
    public function debug($debug = true)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Handle and return response.
     *
     * @return Response
     *
     * @throws BadRequestException
     */
    public function serve()
    {
        Log::debug('Request received:', [
            'Method' => $this->request->getMethod(),
            'URI' => $this->request->getRequestUri(),
            'Query' => $this->request->getQueryString(),
            'Protocal' => $this->request->server->get('SERVER_PROTOCOL'),
            'Content' => $this->request->getContent(),
        ]);

        $this->validate($this->token);

        if ($str = $this->request->get('echostr')) {
            Log::debug("Output 'echostr' is '$str'.");

            return new Response($str);
        }

        $this->handleRequest();

        $response = $this->buildResponse();

        Log::debug('Server response created:', compact('response'));

        return new Response($response);
    }

    /**
     * Validation request params.
     *
     * @param string $token
     *
     * @throws FaultException
     */
    public function validate($token)
    {
        $params = [
            $token,
            $this->request->get('timestamp'),
            $this->request->get('nonce'),
        ];

        if (!$this->debug && $this->request->get('signature') !== $this->signature($params)) {
            throw new FaultException('Invalid request signature.', 400);
        }
    }


    public function setMessageHandler($callback = null, $option = self::ALL_MSG)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Argument #2 is not callable.');
        }

        $this->messageHandler = $callback;
        $this->messageFilter = $option;

        return $this;
    }

    /**
     * Return the message listener.
     *
     * @return string
     */
    public function getMessageHandler()
    {
        return $this->messageHandler;
    }

    /**
     * Set Encryptor.
     *
     * @param Encryptor $encryptor
     *
     * @return $this
     */
    public function setEncryptor(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    /**
     * Return the encryptor instance.
     *
     * @return Encryptor
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    protected function buildResponse()
    {
        return self::SUCCESS_EMPTY_RESPONSE;
    }


    /**
     * Get request message.
     * @return object
     * @throws BadRequestException
     * @throws RuntimeException
     */
    public function getMessage()
    {
        $message = $this->parseMessageFromRequest($this->request->getContent(false));

        if (!is_array($message) || empty($message)) {
            throw new BadRequestException('Invalid request.');
        }

        return $message;
    }

    /**
     * Handle request.
     *
     * @return array
     *
     * @throws \EasyWeChat\Core\Exceptions\RuntimeException
     * @throws \EasyWeChat\Server\BadRequestException
     */
    protected function handleRequest()
    {
        $message = $this->getMessage();
        $this->handleMessage($message);
        return ;
    }

    /**
     * Handle message.
     *
     * @param array $message
     *
     * @return mixed
     */
    protected function handleMessage($message)
    {
        $handler = $this->messageHandler;

        if (!is_callable($handler)) {
            Log::debug('No handler enabled.');

            return;
        }

        Log::debug('Message detail:', $message);

        $message = new Collection($message);

        $type = $this->messageTypeMapping[$message->get('InfoType')];

        $response = null;

        if ($this->messageFilter & $type) {
            $response = call_user_func_array($handler, [$message]);
        }

        return $response;
    }


    /**
     * Get signature.
     *
     * @param array $request
     *
     * @return string
     */
    protected function signature($request)
    {
        sort($request, SORT_STRING);

        return sha1(implode($request));
    }

    /**
     * Parse message array from raw php input.
     *
     * @param string|resource $content
     *
     * @throws \EasyWeChat\Core\Exceptions\RuntimeException
     * @throws \EasyWeChat\Encryption\EncryptionException
     *
     * @return array
     */
    protected function parseMessageFromRequest($content)
    {
        $content = strval($content);

        if ($this->isSafeMode()) {
            if (!$this->encryptor) {
                throw new RuntimeException('Safe mode Encryptor is necessary, please use Guard::setEncryptor(Encryptor $encryptor) set the encryptor instance.');
            }

            $message = $this->encryptor->decryptMsg(
                $this->request->get('msg_signature'),
                $this->request->get('nonce'),
                $this->request->get('timestamp'),
                $content
            );
        } else {
            $message = XML::parse($content);
        }

        return $message;
    }

    /**
     * Check the request message safe mode.
     *
     * @return bool
     */
    private function isSafeMode()
    {
        return $this->request->get('encrypt_type') && $this->request->get('encrypt_type') === 'aes';
    }
}