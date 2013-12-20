<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Utils
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * The class manages XApp connect plugins
 *
 *  Remarks :
 *
 * @package Utils
 * @class XApp_Ranking
 * @error 153
 * @author mc007
 */
class XApp_Ranking {

    /**
     * calculates the score for a link (upvotes - downvotes)
     *
     * @access  private
     * @since   0.1
     * @param   int $upvotes, int $downvotes
     * @return  int
     */
    private function _score($upvotes = 0, $downvotes = 0) {
        return $upvotes - $downvotes;
    }

    /**
     * calculates the hotness of an article
     *
     * @access  private
     * @since   0.1
     * @param   int $upvotes, int $downvotes, int $posted
     * @return  float
     */
    private function _hotness($upvotes = 0, $downvotes = 0, $posted = 0) {
        $s = $this->_score($upvotes, $downvotes);
        $order = log(max(abs($s), 1), 10);

        if($s > 0) {
            $sign = 1;
        } elseif($s < 0) {
            $sign = -1;
        } else {
            $sign = 0;
        }

        $seconds = $posted - 1134028003;

        return round($order + (($sign * $seconds)/45000), 7);
    }

    /**
     *  confidence sort based on http://www.evanmiller.org/how-not-to-sort-by-average-rating.html
     *
     * @since   0.1
     * @access  private
     * @param   int $upvotes, int $downvotes
     * @return  double
     * @see     http://www.evanmiller.org/how-not-to-sort-by-average-rating.html
     */
    private function _confidence($upvotes = 0, $downvotes = 0) {
        $n = $upvotes + $downvotes;

        if($n === 0) {
            return 0;
        }

        $z = 1.281551565545; // 80% confidence
        $p = floor($upvotes) / $n;

        $left = $p + 1/(2*$n)*$z*$z;
        $right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
        $under = 1+1/$n*$z*$z;

        return ($left - $right) / $under;
    }

    /**
     * calculates the controversy score for a link
     *
     * @since   0.1
     * @param   int $upvotes, int $downvotes
     * @access  public
     * @return  float
     */
    public function controversy($upvotes = 0, $downvotes = 0) {
        return ($upvotes + $downvotes) / max(abs($this->_score($upvotes, $downvotes)), 1);
    }

    /**
     * public method to calculate a post's hotness
     *
     * @since   0.1
     * @param   int $upvotes, int $downvotes, int $posted
     * @access  public
     * @return  float
     */
    public function hotness($upvotes, $downvotes, $posted) {
        return $this->_hotness($upvotes, $downvotes, $posted);
    }

    /**
     * public method to calculate a posts confidence
     *
     * @since   0.1
     * @param   int $upvotes, int $downvotes
     * @access  public
     * @return  double
     */
    public function confidence($upvotes, $downvotes) {
        return $this->_confidence($upvotes, $downvotes);
    }
}