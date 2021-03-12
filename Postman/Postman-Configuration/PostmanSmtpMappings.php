<?php

class PostmanSmtpMappings {
    // if an email is in this domain array, it is a known smtp server (easy lookup)
    private static $emailDomain = array (
            // from http://www.serversmtp.com/en/outgoing-mail-server-hostname
            '1and1.com' => 'smtp.1and1.com',
            'airmail.net' => 'smtp.airmail.net',
            'aol.com' => 'smtp.aol.com',
            'Bluewin.ch' => 'Smtpauths.bluewin.ch',
            'Comcast.net' => 'Smtp.comcast.net',
            'Earthlink.net' => 'Smtpauth.earthlink.net',
            'gmail.com' => 'smtp.gmail.com',
            'Gmx.com' => 'mail.gmx.com',
            'Gmx.net' => 'mail.gmx.com',
            'Gmx.us' => 'mail.gmx.com',
            'hotmail.com' => 'smtp.live.com',
            'icloud.com' => 'smtp.mail.me.com',
            'mail.com' => 'smtp.mail.com',
            'ntlworld.com' => 'smtp.ntlworld.com',
            'rocketmail.com' => 'plus.smtp.mail.yahoo.com',
            'rogers.com' => 'smtp.broadband.rogers.com',
            'yahoo.ca' => 'smtp.mail.yahoo.ca',
            'yahoo.co.id' => 'smtp.mail.yahoo.co.id',
            'yahoo.co.in' => 'smtp.mail.yahoo.co.in',
            'yahoo.co.kr' => 'smtp.mail.yahoo.com',
            'yahoo.com' => 'smtp.mail.yahoo.com',
            'yahoo.com.ar' => 'smtp.mail.yahoo.com.ar',
            'yahoo.com.au' => 'smtp.mail.yahoo.com.au',
            'yahoo.com.br' => 'smtp.mail.yahoo.com.br',
            'yahoo.com.cn' => 'smtp.mail.yahoo.com.cn',
            'yahoo.com.hk' => 'smtp.mail.yahoo.com.hk',
            'yahoo.com.mx' => 'smtp.mail.yahoo.com',
            'yahoo.com.my' => 'smtp.mail.yahoo.com.my',
            'yahoo.com.ph' => 'smtp.mail.yahoo.com.ph',
            'yahoo.com.sg' => 'smtp.mail.yahoo.com.sg',
            'yahoo.com.tw' => 'smtp.mail.yahoo.com.tw',
            'yahoo.com.vn' => 'smtp.mail.yahoo.com.vn',
            'yahoo.co.nz' => 'smtp.mail.yahoo.com.au',
            'yahoo.co.th' => 'smtp.mail.yahoo.co.th',
            'yahoo.co.uk' => 'smtp.mail.yahoo.co.uk',
            'yahoo.de' => 'smtp.mail.yahoo.de',
            'yahoo.es' => 'smtp.correo.yahoo.es',
            'yahoo.fr' => 'smtp.mail.yahoo.fr',
            'yahoo.ie' => 'smtp.mail.yahoo.co.uk',
            'yahoo.it' => 'smtp.mail.yahoo.it',
            'zoho.com' => 'smtp.zoho.com',
            // from http://www.att.com/esupport/article.jsp?sid=KB401570&cv=801
            'ameritech.net' => 'outbound.att.net',
            'att.net' => 'outbound.att.net',
            'bellsouth.net' => 'outbound.att.net',
            'flash.net' => 'outbound.att.net',
            'nvbell.net' => 'outbound.att.net',
            'pacbell.net' => 'outbound.att.net',
            'prodigy.net' => 'outbound.att.net',
            'sbcglobal.net' => 'outbound.att.net',
            'snet.net' => 'outbound.att.net',
            'swbell.net' => 'outbound.att.net',
            'wans.net' => 'outbound.att.net' 
    );
    // if an email's mx is in this domain array, it is a known smtp server (dns lookup)
    // useful for custom domains that map to a mail service
    private static $mxMappings = array (
            '1and1help.com' => 'smtp.1and1.com',
            'google.com' => 'smtp.gmail.com',
            'Gmx.net' => 'mail.gmx.com',
            'icloud.com' => 'smtp.mail.me.com',
            'hotmail.com' => 'smtp.live.com',
            'mx-eu.mail.am0.yahoodns.net' => 'smtp.mail.yahoo.com',
            // 'mail.protection.outlook.com' => 'smtp.office365.com',
            // 'mail.eo.outlook.com' => 'smtp.office365.com',
            'outlook.com' => 'smtp.office365.com',
            'biz.mail.am0.yahoodns.net' => 'smtp.bizmail.yahoo.com',
            'BIZ.MAIL.YAHOO.com' => 'smtp.bizmail.yahoo.com',
            'hushmail.com' => 'smtp.hushmail.com',
            'gmx.net' => 'mail.gmx.com',
            'mandrillapp.com' => 'smtp.mandrillapp.com',
            'smtp.secureserver.net' => 'relay-hosting.secureserver.net',
            'presmtp.ex1.secureserver.net' => 'smtp.ex1.secureserver.net',
            'presmtp.ex2.secureserver.net' => 'smtp.ex2.secureserver.net',
            'presmtp.ex3.secureserver.net' => 'smtp.ex2.secureserver.net',
            'presmtp.ex4.secureserver.net' => 'smtp.ex2.secureserver.net',
            'htvhosting.com' => 'mail.htvhosting.com' 
    );
    /**
     * @param false|string $hostname
     */
    public static function getSmtpFromEmail($hostname) {
        reset ( PostmanSmtpMappings::$emailDomain );
        foreach ( PostmanSmtpMappings::$emailDomain as $domain => $smtp ) {
            if (strcasecmp ( $hostname, $domain ) == 0) {
                return $smtp;
            }
        }
        return false;
    }
    /**
     * @param string $mx
     */
    public static function getSmtpFromMx($mx) {
        reset ( PostmanSmtpMappings::$mxMappings );
        foreach ( PostmanSmtpMappings::$mxMappings as $domain => $smtp ) {
            if (PostmanUtils::endswith ( $mx, $domain )) {
                return $smtp;
            }
        }
        return false;
    }
}