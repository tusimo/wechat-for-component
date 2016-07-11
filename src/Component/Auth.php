<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 16:25
 */

namespace Tusimo\Wechat\Component;
use Overtrue\Socialite\Providers\WeChatProvider;


/**
 * OAuth 网页授权获取用户信息
 */
class Auth extends WeChatProvider
{
    const API_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const API_TOKEN_GET = 'https://api.weixin.qq.com/sns/oauth2/component/access_token'; //请求CODE
    const API_TOKEN_REFRESH = 'https://api.weixin.qq.com/sns/oauth2/component/refresh_token'; //通过code换取access_token
    const API_USER = 'https://api.weixin.qq.com/sns/userinfo'; //刷新access_token
    protected $componentId;
    
//    /**
//     * 生成oAuth URL
//     *
//     * @param string $to
//     * @param string $scope
//     * @param string $state
//     * @return string
//     */
//    public function url($to = null, $scope = 'snsapi_userinfo', $state = 'STATE')
//    {
//        $to !== null || $to = Url::current();
//        $params = array(
//            'appid'           => $this->clientId,
//            'redirect_uri'    => $to,
//            'response_type'   => 'code',
//            'scope'           => $scope,
//            'state'           => $state,
//            'component_appid' => $this->componentId,
//        );
//        return self::API_URL . '?' . http_build_query($params) . '#wechat_redirect';
//    }
//    public function user()
//    {
//        $code = $this->input->get('code');
//        $this->appId = $this->input->get('appid');
//        if ($this->authorizedUser || !$code || !$this->appId) {
//            return $this->authorizedUser;
//        }
//        $permission = $this->getAccessPermission($code);
//        if ($permission['scope'] !== 'snsapi_userinfo') {
//            $user = new Bag(array('openid' => $permission['openid']));
//        } else {
//            $user = $this->getUser($permission['openid'], $permission['access_token']);
//        }
//        return $this->authorizedUser = $user;
//    }
    public function getAccessPermission($code)
    {
        $params = array(
            'appid'           => $this->appId,
            'code'            => $code,
            'grant_type'      => 'authorization_code',
            'component_appid' => $this->component_appid,
        );
        return $this->lastPermission = $this->http->get(self::API_TOKEN_GET, $params);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getAuthUrl($state)
    {
        $path = 'oauth2/authorize';

        if (in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}", $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'appid'         => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state'         => $state,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'/oauth2/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $scopes = explode(',', $token->getAttribute('scope', ''));

        if (in_array('snsapi_base', $scopes)) {
            return $token->toArray();
        }

        $response = $this->getHttpClient()->get($this->baseUrl.'/userinfo', [
            'query' => [
                'access_token' => $token->getToken(),
                'openid'       => $token['openid'],
                'lang'         => 'zh_CN',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}.
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id'       => $this->arrayItem($user, 'openid'),
            'name'     => $this->arrayItem($user, 'nickname'),
            'nickname' => $this->arrayItem($user, 'nickname'),
            'avatar'   => $this->arrayItem($user, 'headimgurl'),
            'email'    => null,
        ]);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return [
            'appid'      => $this->clientId,
            'secret'     => $this->clientSecret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody()->getContents());
    }

    /**
     * {@inheritdoc}.
     */
    protected function parseAccessToken($body)
    {
        return new AccessToken(json_decode($body, true));
    }

    /**
     * Remove the fucking callback parentheses.
     *
     * @param mixed $response
     *
     * @return string
     */
    protected function removeCallback($response)
    {
        if (strpos($response, 'callback') !== false) {
            $lpos     = strpos($response, '(');
            $rpos     = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        return $response;
    }
}