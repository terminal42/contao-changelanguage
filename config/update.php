<?php

if (0 !== count(array_intersect(ModuleLoader::getActive(), array('newslanguage', 'calendarlanguage')))) {
    die('changelanguage v3 is not compatible with newslanguage and calendarlanguage. Please remove system/modules/newslanguage and/or system/modules/calendarlanguage');
}
