<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Tests configuration file.
 *
 * PHP version 5.1.0+
 *
 * @category  Services
 * @package   Services_Twitter
 * @author    Joe Stump <joe@joestump.net> 
 * @author    David Jean Louis <izimobil@gmail.com> 
 * @copyright 1997-2008 Joe Stump <joe@joestump.net> 
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://twitter.com/help/api
 * @link      http://twitter.com
 */


/**
 * Dependencies.
 */
require_once 'Services/Twitter.php';
require_once 'HTTP/Request2/Response.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'HTTP/OAuth/Consumer.php';

$cfgfile = dirname(__FILE__) . '/tests-config.php';
if (false === @include_once $cfgfile) {
    // default config
    $config = array(
        'user'                  => 'test_user',
        'pass'                  => 'test_password',
        'friend'                => 'test_friend',
        'status_id'             => 100000,
        'live_test'             => false,
        'list_name'             => 'test-list',
        'list_slug'             => 'test-list',
        'list_user'             => 100000,
        'oauth_test'            => true,
        'consumer_key'          => 'xxxx',
        'consumer_secret'       => 'xxxx',
        'access_token'          => 'xxxx',
        'access_token_secret'   => 'xxxx',
    );
}

/**
 * Returns an instance of Services_Twitter.
 *
 * @param string $ep      The endpoint to call (eg. )
 * @param bool   $auth    Whether to authenticate or not
 * @param array  $options An optional options array to pass to the 
 *                        Services_Twitter constructor
 *
 * @return Services_Twitter The twitter instance.
 */
function Services_Twitter_factory($ep, $auth = true, $options = array())
{
    //$options['raw_format'] = true;
    global $config;
    if ($auth) {
        if ($config['oauth_test']) {
            $twitter = new Services_Twitter(null, null, $options);
            $oauth   = new HTTP_OAuth_Consumer($config['consumer_key'],
                                               $config['consumer_secret'],
                                               $config['access_token'],
                                               $config['access_token_secret']);
            $twitter->setOAuth($oauth);
        } else {
            $twitter = new Services_Twitter($config['user'], $config['pass'], $options);
        }
    } else {
        $twitter = new Services_Twitter(null, null, $options);
    }
    
    if (!$config['live_test']) {
        if ($ep == 'exception1') {
            $resp = new HTTP_Request2_Response('HTTP/1.1 401 Unauthorized', false);
            $resp->appendBody(
                '{"request":"\/statuses\/friends_timeline.json", '
                . '"error":"Could not authenticate you."}'
            );
        } else if ($ep == 'exception2') {
            $resp = new HTTP_Request2_Response('HTTP/1.1 404 Not Found', false);
        } else {
            $resp = new HTTP_Request2_Response('HTTP/1.1 200 Success', false);
            $file = dirname(__FILE__) . '/data/' . $ep . '.dat';
            $resp->appendBody(file_get_contents($file));
        }
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse($resp);
        $request = $twitter->getRequest()->setAdapter($mock);
    }
    return $twitter;
}
