<?php


namespace Siarko\cli\ui\components;

use Siarko\cli\ui\components\base\Component;
use Siarko\cli\util\BoundingBox;

class Container extends Component
{

    /**
     * @param BoundingBox $contentBox
     */
    public function drawContent(BoundingBox $contentBox)
    {
        /*
         * Containers have no custom content, only children
         * */
    }
}