<?php

namespace GroupBot\Brains\Coin;

class Feedback
{
	private $feedback = "";
	private $feedbackCodes = array();

	private $feedbackMessages = array(
		0  => "Wrong password.",
		1  => "This user does not exist.",
		2  => "",
		3  => "Empty Username.",
		4  => "Empty Password",
		5  => "An unknown error occurred.",
		6  => "You were just logged out.",
		7  => "",
		8  => "",
		9  => "",
		10 => "Password and password repeat are not the same.",
		11 => "Password has a minimum length of 6 characters.",
		12 => "Username cannot be shorter than 2 or longer than 64 characters.",
		13 => "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters.",
		14 => "Sorry, that username is already taken. Please choose another one.",
		15 => "Your account has been created successfully. You can now log in.",
		16 => "Sorry, your registration failed. Please go back and try again.",
		17 => "",
		18 => "",
		19 => "",
		20 => "Invalid amount entered.",
		21 => "Can't send " . COIN_CURRENCY_NAME . " to yourself!",
		22 => "Recipient field was empty.",
		23 => "Amount field was empty.",
		24 => "Isaac Coin transferred.",
		25 => "Transfer botched. Oops.",
		26 => "Transfer failed.",
		27 => "You don't have enough " . COIN_CURRENCY_NAME . "!",
		28 => "Couldn't retrieve " . COIN_CURRENCY_NAME . " balance.",
		29 => "",
		30 => "",
		31 => "Linking error encountered.",
		32 => "You must be logged in to link an account to Telegram!",
		33 => "You've already got a Coin account!",
		34 => "Unlinked from Telegram.",
		35 => "Malformed URL.",
		36 => "Invalid or old URL. Request a new one from Telegram.",
		37 => "",
		38 => "",
		39 => "",
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
			$out .= $this->feedbackMessages[$i] . '<br />';
		}

		$out = rtrim($out, "<br />");

		return $this->feedback . (strlen($this->feedback) > 0 ? "\n" . $out : $out);
	}
}