<?php

interface PostmanZendMailTransportConfigurationFactory {
	static function createConfig(PostmanZendModuleTransport $transport);
}