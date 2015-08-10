<?php
abstract class VelocityCon {

  const VERSION = '1.0';

}
require_once '../includes/sdk/configuration.php';
require_once '../includes/sdk/Velocity/Helpers.php';
require_once '../includes/sdk/Velocity/Errors.php';
require_once '../includes/sdk/Velocity/XmlParser.php';
require_once '../includes/sdk/Velocity/Message.php';
require_once '../includes/sdk/Velocity/XmlCreator.php';
require_once '../includes/sdk/Velocity/Connection.php';
require_once '../includes/sdk/Velocity/Transaction.php';
require_once '../includes/sdk/Velocity/Processor.php';

/* 
 * check php version if below 5.2.1 then throw exception msg.
 */
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  throw new Exception('PHP version >= 5.2.1 required');
}

/* 
 * check the dependency of curl, simplexml, openssl loaded or not.
 */
function checkDependencies(){
  $extensions = array('curl', 'SimpleXML', 'openssl');
  foreach ($extensions AS $ext) {
    if (!extension_loaded($ext)) {
      throw new Exception('Velocity-client-php requires the ' . $ext . ' extension.');
    }
  }
}

checkDependencies();

