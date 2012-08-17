<?php

$nameCode7F = array();

$nameCode7F[0x02] = array(
						"requires" => 0,
						"description" => "New page - clears the current text and starts at a new page. This does not automatically add a text break."
						);

$nameCode7F[0x04] = array(
						"requires" => 0,
						"description" => "Text break - pauses the text display until a button is pressed."
						);

$nameCode7F[0x05] = array(
						"requires" => 3,
						"description" => "Changes the color of a string of text.",
						0 => array( "length" => 1, "description" => "red" ),
						1 => array( "length" => 1, "description" => "green" ),
						2 => array( "length" => 1, "description" => "blue" )
						);

$nameCode7F[0x09] = array(
						"requires" => 3,
						"description" => "Changes the expression of the villager talking to you.",
						0 => array( "length" => 3, "description" => "expression" )
						);

$nameCode7F[0x1D] = array(
						"requires" => 0,
						"description" => "The current year."
						);

$nameCode7F[0x1E] = array(
						"requires" => 0,
						"description" => "The current month."
						);

$nameCode7F[0x1F] = array(
						"requires" => 0,
						"description" => "The current day of the week."
						);

$nameCode7F[0x25] = array(
						"requires" => 0,
						"description" => "Variable, used in letters as the name of the villager writing you."
						);

$nameCode7F[0x3F] = array(
						"requires" => 0,
						"description" => "Variable"
						);

$nameCode7F[0x50] = array(
						"requires" => 4,
						"description" => "Changes the color of a string of text with a given length.",
						0 => array( "length" => 1, "description" => "red" ),
						1 => array( "length" => 1, "description" => "green" ),
						2 => array( "length" => 1, "description" => "blue" ),
						3 => array( "length" => 1, "description" => "length" )
						);

$nameCode7F[0x50] = array(
						"requires" => 1,
						"description" => "Sets the animal speech of the current conversation.",
						0 => array( "length" => 1, "description" => "00 - " )
						);

$nameCode7F[0x76] = array(
						"requires" => 0,
						"description" => "a.m. or p.m."
						);
