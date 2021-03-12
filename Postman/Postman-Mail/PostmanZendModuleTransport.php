<?php

interface PostmanZendModuleTransport extends PostmanModuleTransport {
	public function getAuthenticationType();
	public function getSecurityType();
	public function getCredentialsId();
	public function getCredentialsSecret();
	public function getEnvelopeFromEmailAddress();
}