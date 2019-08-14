<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

error_reporting(E_ALL & ~E_NOTICE);

define('THIS_SCRIPT', 'aitmagentovb');

header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
//header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); 

require_once('./global.php');
require_once(DIR . '/includes/class_aitmagentovb.php');
require_once(DIR . '/includes/class_aitzend.php');

$vbulletin->input->clean_array_gpc('r', array(
    'h' => TYPE_STR,
));

if (empty($_REQUEST['do']))
{
    exec_header_redirect($vbulletin->options['forumhome'] . '.php');
}

if ($_REQUEST['do'] == 'login')
{
    /**
     * Log in user by userid
     * $_REQUEST['userid'] - userid
     */
    $vbulletin->input->clean_array_gpc('r', array(
        'userid' => TYPE_INT,
    ));
    
    if ($vbulletin->GPC['userid'] AND
        Aitmagentovb::isValidApikeyHash($vbulletin->GPC['h'], $_REQUEST['do'] . $vbulletin->GPC['userid']) AND
        $loguser = fetch_userinfo($vbulletin->GPC['userid']) AND
        $vbulletin->userinfo['userid'] != $loguser['userid'] ) // no need to login if user is already authorized
    {
        require_once(DIR . '/includes/functions_login.php');
        
        exec_unstrike_user($loguser['username']);
        
        $vbulletin->userinfo['userid'] = $loguser['userid'];
        process_new_login('', false, '');
        
        exec_shut_down();
    }
    
    Aitmagentovb::outputBlankGif();
}
elseif ($_REQUEST['do'] == 'logout')
{
    /**
     * Log out user by userid
     * $_REQUEST['userid'] - userid
     */
    $vbulletin->input->clean_array_gpc('r', array(
        'userid' => TYPE_INT,
    ));
    
    if ($vbulletin->GPC['userid'] == $vbulletin->userinfo['userid'] AND
        Aitmagentovb::isValidApikeyHash($vbulletin->GPC['h'], $_REQUEST['do'] . $vbulletin->GPC['userid']) )
    {
        require_once(DIR . '/includes/functions_login.php');
        
        process_logout();
        
        exec_shut_down();
    }
    
    Aitmagentovb::outputBlankGif();
}
elseif ($_REQUEST['do'] == 'verify')
{
    /**
     * Verify user 
     * $_POST['username'] - forum username
     * $_POST['password_md5'] - password md5 hash
     * @since 0.3.0
     */
    $vbulletin->input->clean_array_gpc('p', array(
        'username'      => TYPE_STR,
        'password_md5'  => TYPE_STR,
    ));
    
    if ($vbulletin->GPC['username'] AND 
        $vbulletin->GPC['password_md5'] AND 
        Aitmagentovb::isValidApikeyHash($vbulletin->GPC['h'], $_REQUEST['do'] . $vbulletin->GPC['username']) )
    {
        require_once(DIR . '/includes/functions_login.php');
        
        if (!verify_authentication($vbulletin->GPC['username'], '', $vbulletin->GPC['password_md5'], '', false, false))
        {
            exec_header_redirect($vbulletin->options['forumhome'] . '.php');
        }
        
        $aitmagentovbResult = array();
        $aitmagentovbResult['userid'] = $vbulletin->userinfo['userid'];
        $aitmagentovbResult['email']  = $vbulletin->userinfo['email'];
        $aitmagentovbResult['done']   = 'verify';
        
        Aitmagentovb::outputJsonResult($aitmagentovbResult);
        
        exec_shut_down();
    }
    else 
    {
        exec_header_redirect($vbulletin->options['forumhome'] . '.php');
    }
}
else if ($_REQUEST['do'] == 'ping')
{
    $aitmagentovbResult = array('THIS_SCRIPT' => THIS_SCRIPT);
    
    Aitmagentovb::outputJsonResult($aitmagentovbResult, true);
}
else if ($_POST['do'] == 'options')
{
    if (Aitmagentovb::isValidApikeyHash($vbulletin->GPC['h'], $_POST['do']) )
    {
        $aitmagentovbResult = $vbulletin->options;
        $aitmagentovbResult['bf_misc'] = $vbulletin->bf_misc; /** @since 0.2.2 */
        $aitmagentovbResult['bf_ugp']  = $vbulletin->bf_ugp; /** @since 0.3.0 */
        $aitmagentovbResult['done'] = 'options';
        
        Aitmagentovb::outputJsonResult($aitmagentovbResult, true);
    }
    else 
    {
        exec_header_redirect($vbulletin->options['forumhome'] . '.php');
    }
}
