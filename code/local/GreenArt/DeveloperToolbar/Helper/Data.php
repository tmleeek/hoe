<?php
class GreenArt_DeveloperToolbar_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function AllDirChmod( $dir = "./", $dirModes = 0755, $fileModes = 0644 ){
       $d = new RecursiveDirectoryIterator( $dir );
       foreach( new RecursiveIteratorIterator( $d, 1 ) as $path ){
          if( $path->isDir() ) chmod( $path, $dirModes );
          else if( is_file( $path ) ) chmod( $path, $fileModes );
      }
    }

    ## Function to clean out the contents of specified directory

    private function getCleandir($dir) {
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && is_file($dir.'/'.$file)) {
                    if (unlink($dir.'/'.$file)) { }
                    else { echo $dir . '/' . $file . ' (file) NOT deleted!<br />'; }
                }
                else if ($file != '.' && $file != '..' && is_dir($dir.'/'.$file)) {
                    echo $this->getCleandir($dir.'/'.$file);
                    if (rmdir($dir.'/'.$file)) {}
                    else { echo $dir . '/' . $file . ' (directory) NOT deleted!<br />'; }
                }
            }
            closedir($handle);
        }

    }
    public function getDeveloperToolbar()
    {
      if(isset($_GET['cleanup']) == 1) {
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/cache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/cache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/session")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/session"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/minifycache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/minifycache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/cache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/cache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/download")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/download"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/pear.ini")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/pear.ini"); }
          echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['cleanup']) == 1) {
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/cache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/cache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/minifycache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/minifycache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/cache")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/cache"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/download")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/download"); }
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/pear.ini")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/downloader/pearlib/pear.ini"); }
          echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['cleanup']) == 3) {
          if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/session")) { Mage::helper('developertoolbar')->getCleandir($_SERVER['DOCUMENT_ROOT']."/var/session"); }
          echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['permision']) == 1)
      {
        Mage::helper('developertoolbar')->AllDirChmod( "." );
        chmod("./lib/PEAR", 550);
        echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['permision']) == 2)
      {
        Mage::helper('developertoolbar')->AllDirChmod( "." );
        echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['permision']) == 3)
      {
        chmod("./lib/PEAR", 550);
        echo "<script type='text/javascript'>window.location = '/'</script>";
      }
      if(isset($_GET['clear-log']) == 1)
      {
        if (file_exists($_SERVER['DOCUMENT_ROOT']."/var/log")) { @unlink($_SERVER['DOCUMENT_ROOT'].'/var/log/system.log'); }
      }
    }

    function getPrintArray ( $what ) {
        echo '<pre>'; print_r ( $what ); echo '</pre>';
    }

    function extension_check($extensions) {
    	$fail = '';
    	$pass = '';

    	if(version_compare(phpversion(), '5.2.0', '<')) {
    		$fail .= '<li>You need<strong> PHP 5.2.0</strong> (or greater)</li>';
    	}
    	else {
    		$pass .='<li>You have<strong> PHP 5.2.0</strong> (or greater)</li>';
    	}

    	if(!ini_get('safe_mode')) {
    		$pass .='<li>Safe Mode is <strong>off</strong></li>';
    		preg_match('/[0-9]\.[0-9]+\.[0-9]+/', shell_exec('mysql -V'), $version);

    		if(version_compare($version[0], '4.1.20', '<')) {
    			$fail .= '<li>You need<strong> MySQL 4.1.20</strong> (or greater)</li>';
    		}
    		else {
    			$pass .='<li>You have<strong> MySQL 4.1.20</strong> (or greater)</li>';
    		}
    	}
    	else { $fail .= '<li>Safe Mode is <strong>on</strong></li>';  }

    	foreach($extensions as $extension) {
    		if(!extension_loaded($extension)) {
    			$fail .= '<li> You are missing the <strong>'.$extension.'</strong> extension</li>';
    		}
    		else{	$pass .= '<li>You have the <strong>'.$extension.'</strong> extension</li>';
    		}
    	}

    	if($fail) {
    		echo '<p><strong>Your server does not meet the following requirements in order to install Magento.</strong>';
    		echo '<br>The following requirements failed, please contact your hosting provider in order to receive assistance with meeting the system requirements for Magento:';
    		echo '<ul>'.$fail.'</ul></p>';
    		echo 'The following requirements were successfully met:';
    		echo '<ul>'.$pass.'</ul>';
    	} else {
    		echo '<p><strong>Congratulations!</strong> Your server meets the requirements for Magento.</p>';
    		echo '<ul>'.$pass.'</ul>';

    	}
    }

}