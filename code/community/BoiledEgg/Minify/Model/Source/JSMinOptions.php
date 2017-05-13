<?php
/**
 * Class BoiledEgg_Minify_Model_Source_JSMinOptions
 */
class BoiledEgg_Minify_Model_Source_JSMinOptions
{
	public function toOptionArray()
	{
		return array(
			array(
				'label' => 'JSMin',
				'value' => 'jsmin',
			),
			array(
				'label' => 'JSMinPlus',
				'value' => 'jsminplus',
			),
		);
	}
}