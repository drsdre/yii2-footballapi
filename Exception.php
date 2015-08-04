<?php
namespace FootballAPI;
/**
 * Class Exception
 *
 * @author Andre Schuurman <andre.schuurman@gmail.com>
 */
class Exception extends \yii\base\Exception
{
	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'FootballAPI Client Exception';
	}
}