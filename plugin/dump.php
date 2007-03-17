<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.plugin.helper' );

if( file_exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_dump'.DS.'helper.php' ) ) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_dump'.DS.'helper.php' );
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_dump'.DS.'defines.php' );
} else {
    JError::raiseNotice( 20, 'The Dump Plugin needs the Dump Component to function.' );
}

class DumpPlugin extends JPlugin {
    function dumpPlugin(& $subject) {
        parent::__construct($subject);
    }

    function onAfterDisplay() {
        global $mainframe;

        // settings from config.xml
        $dumpConfig = & JComponentHelper::getParams( 'com_dump' );
        $autopopup  = $dumpConfig->get( 'autopopup', 1 );

        $userstate = $mainframe->getUserState( 'dump.nodes' );
        $cnt_dumps  = count( $userstate );

        if( $autopopup AND $cnt_dumps) {
            DumpHelper::showPopup();
        }
    }
}

// create dispatcher and attach object to dispatcher
$dispatcher = & JEventDispatcher::getInstance();
$dispatcher->attach( new DumpPlugin( $dispatcher ) );

/**
 * Add a variable to the list of variables that will be shown in the debug window
 * @param mixed $var The variable you want to dump
 * @param mixed $name The name of the variable you want to dump
 */
function dump( $var = null, $name = '(unknown name)', $type = null, $level = 0 ) {
    global $mainframe;

    include_once( JPATH_PLUGINS.DS.'system'.DS.'dump'.DS.'node.php' );

    // create a new node array
    $node           = & DumpNode::getNode( $var, $name, $type, $level );
    //get the current userstate
    $userstate      = $mainframe->getUserState( 'dump.nodes' );
    // append the node to the array
    $userstate[]    = $node;
    // set the userstate to the new array
    $mainframe->setUserState( 'dump.nodes', $userstate );
}

/**
 * Shortcut to dump the parameters of a template
 * @param object $var The "$this" object in the template
 */
function dumpTemplate( $var, $name = false ) {
    $name = $name ? $name :  $var->template;
    dump( $var->params->_registry['parameter']['data'], $name );
}

/**
 * Shortcut to dump a message
 * @param string $msg The message
 */
function dumpMessage( $msg = '(Empty message)' ) {
    dump( $msg, null, 'message', 0 );
}

/**
 * Shortcut to dump system information
 */
function dumpSysinfo() {
    include_once( JPATH_PLUGINS.DS.'system'.DS.'dump'.DS.'sysinfo.php' );
    $sysinfo = new DumpSysinfo();
    dump( $sysinfo->data, 'System Information');
}