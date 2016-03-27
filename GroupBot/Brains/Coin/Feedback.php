<?php

namespace GroupBot\Brains\Coin;

class Feedback
{
	private $feedback = "";
	private $feedbackCodes = array();

	private $feedbackMessages = array(
		20 => "Invalid amount entered.",
		21 => "Can't send " . COIN_CURRENCY_NAME . " to yourself!",
		24 => COIN_CURRENCY_NAME . " transferred.",
		25 => "Transfer botched. Oops.",
		26 => "Transfer failed.",
		27 => "You don't have enough " . COIN_CURRENCY_NAME . "!",
		28 => "Couldn't retrieve " . COIN_CURRENCY_NAME . " balance.",
		40 => "Special error."
	);

    public function __construct()
    {
		if (isset($_GET['status']))
		{
			$codes = explode(",", $_GET['status']);

			foreach ($codes as $i)
			{
				array_push($this->feedbackCodes, $i);
			}
		}
    }
	
	public function isFeedback()
	{
		return !empty($this->feedbackCodes);
	}

	public function addFeedbackCode($code)
	{
		array_push($this->feedbackCodes, $code);
		return true;
	}

	public function addFeedback($message)
	{
		$this->feedback .= (strlen($this->feedback) > 0 ? "\n" : "") . $message;
	}

	public function getFeedbackCodes()
	{
		$out = "";

		foreach( $this->feedbackCodes as $i)
		{
			$out .= $i . ',';
		}

		return rtrim($out, ",");
	}

	public function getFeedback()
	{
		$out = "";

		foreach( $this->feedbackCodes as $i)
		{
			$out .= $this->feedbackMessages[$i] . "\n";
		}

		$out = rtrim($out, "\n");

		return $this->feedback . (strlen($this->feedback) > 0 ? "\n" . $out : $out);
	}
}