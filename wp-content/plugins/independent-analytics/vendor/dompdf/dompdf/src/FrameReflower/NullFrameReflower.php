<?php

/**
 * @package dompdf
 * @link    https://github.com/dompdf/dompdf
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
namespace IAWP_SCOPED\Dompdf\FrameReflower;

use IAWP_SCOPED\Dompdf\Frame;
use IAWP_SCOPED\Dompdf\FrameDecorator\Block as BlockFrameDecorator;
/**
 * Dummy reflower
 *
 * @package dompdf
 */
class NullFrameReflower extends AbstractFrameReflower
{
    /**
     * NullFrameReflower constructor.
     * @param Frame $frame
     */
    function __construct(Frame $frame)
    {
        parent::__construct($frame);
    }
    /**
     * @param BlockFrameDecorator|null $block
     */
    function reflow(BlockFrameDecorator $block = null)
    {
        return;
    }
}
