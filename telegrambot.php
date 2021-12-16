<?php
/*
 *   TelegramBot Plugin
 *   Copyright (C) 2021  Sergey Bunin
 *   Released under GNU General Public License version 3 or later
 */
defined('_JEXEC') or die;

class PlgContentTelegrambot extends JPlugin
{
	/* Load language */
	protected $autoloadLanguage = true;
	
	/* Set data */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$link = $this->params->get('telegrambot_link', false);
		$token = $this->params->get('telegrambot_token', false);
		$channel = $this->params->get('telegrambot_channel', false);
		
		if ($article->state) { // we post only if the material is published
			$this->sendTelegram($link, $token, $channel, $article);	
		}
	}
  
	/* Send to telegram channel */
	public function sendTelegram($link, $token, $channel, $article)
	{
		$url = 'https://api.telegram.org/bot' . $token . '/sendPhoto';

		/* Create image */
		$images  = json_decode($article->get("images"));
		$image   = $images->image_intro;
		
		/* Create text */
		$text = $article->title;
		
		/* Create buttons */
		$inlinekeys[] = array(
			array(
				"text" => JText::_('PLG_TELEGRAMBOT_CHANNEL_SITEBUTTON'),
				"url" => JRoute::_($link . "index.php?option=com_content&view=article&id=" . $article->id)
			)
		);
		$inlinekeyboard = array("inline_keyboard" => $inlinekeys);
		
		/* Create content */
		$content = array(
			"chat_id" => $channel,
			"caption" => $text,
			"photo" => $link . $image,
			"reply_markup" => $inlinekeyboard
		);
					
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
	}
}
