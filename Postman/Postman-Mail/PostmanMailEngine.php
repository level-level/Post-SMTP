<?php

interface PostmanMailEngine {
	public function getTranscript();
	public function send(PostmanMessage $message);
}
