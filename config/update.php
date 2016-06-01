<?php

if (0 !== count(array_intersect(ModuleLoader::getActive(), array('newslanguage', 'calendarlanguage')))) {
    throw new LogicException('ChangeLanguage v3 is not compatible with newslanguage and calendarlanguage. Please remove system/modules/newslanguage and/or system/modules/calendarlanguage');
}
