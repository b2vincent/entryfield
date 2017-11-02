<?php

// Include needed files

// Wrapper for require function, allow specialization for various configurations
// Example usage : set $GLOBALS['configmode'] to 'Specific'  (in Tdl_ConfigLocal)
// this script include files like Tdl_Extends_Specific, Tdl_Schema_Specific, f they are found.   
function tdlRequire($fname) 
{    
    if (is_file($fname)) {
        require_once($fname);
    }
    $fprefix = pathinfo($fname, PATHINFO_DIRNAME) . '/' . pathinfo($fname, PATHINFO_FILENAME);

    $configfname = $fprefix.'_'.$GLOBALS['configmode'].'.php';

    if (is_file($configfname)) {
        require_once($configfname);
    } 
}

tdlRequire($basepath.'/extends/Tdl_Extends.php');
tdlRequire($basepath.'/extends/Tdl_Field.php');
tdlRequire($basepath.'/extends/Tdl_ListViewExtended.php');
tdlRequire($basepath.'/models/Tdl_Model.php');
tdlRequire($basepath.'/lang/Tdl_Lang_'.$language.'.php');
tdlRequire($basepath.'/models/Tdl_Schema.php');
tdlRequire($basepath.'/helpers/Tdl_HelperEtc.php');
tdlRequire($basepath.'/helpers/Tdl_HelperRender.php');
tdlRequire($basepath.'/pages/Tdl_Routes.php');


?>