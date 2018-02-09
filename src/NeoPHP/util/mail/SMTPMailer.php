<?php

namespace NeoPHP\util\mail;

use Exception;

/**
 * SMTPMailer requiere al menos un servidor SMTP en el servidor local
 * Pasos para instalar Postfix en servidor ubuntu:
 * sudo apt-get install postfix
 * sudo dpkg-reconfigure postfix
 */
class SMTPMailer
{   
    private $server;
    private $port;
    private $username;
    private $password;
    private $secure;
    private $from = null;
    private $recipients = array();
    private $cc = array();
    private $bcc = array();
    private $subject = null;
    private $message = null;
    private $attachments = array();
    private $charset = "UTF-8";
    private $contentType = "text/html";
    private $transferEncodeing = "quoted-printable";
    private $altBody = "";

    public function __construct($server="localhost", $port=25, $username = null, $password = null, $secure = null)
    {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->secure = $secure;
    }

    public function setServer ($server)
    {
        $this->server = $server;
    }
    
    public function getServer ()
    {
        return $this->server;
    }
    
    public function setPort ($port)
    {
        $this->port = $port;
    }
    
    public function getPort ()
    {
        return $this->port;
    }
    
    public function setUsername ($username)
    {
        $this->username = $username;
    }
    
    public function getUsername ()
    {
        return $this->username;
    }
    
    public function setPassword ($password)
    {
        $this->password = $password;
    }
    
    public function getPassword ()
    {
        return $this->password;
    }
    
    public function setFrom ($from)
    {
        $this->from = $from;
    }
    
    public function getFrom ()
    {
        return $this->from;
    }
    
    public function addRecipient ($recipient)
    {
        $this->recipients[] = $recipient;
    }
    
    public function getRecipients ()
    {
        return $this->recipients;
    }
    
    public function addCC ($recipient)
    {
        $this->cc[] = $recipient;
    }
    
    public function getCC ()
    {
        return $this->cc;
    }
    
    public function addBCC ($recipient)
    {
        $this->bcc[] = $recipient;
    }
    
    public function getBCC ()
    {
        return $this->bcc;
    }
    
    public function setSubject ($subject)
    {
        $this->subject = $subject;
    }
    
    public function getSubject ()
    {
        return $this->subject;
    }
    
    public function setMessage ($message)
    {
        $this->message = $message;
    }
    
    public function getMessage ()
    {
        return $this->message;
    }
    
    public function addAttachment ($attachment)
    {
        $this->attachments[] = $attachment;
    }

    public function getAttachments ()
    {
        return $this->attachments;
    }
    
    function getCharset()
    {
        return $this->charset;
    }

    function getContentType()
    {
        return $this->contentType;
    }

    function setCharset($charset)
    {
        $this->charset = $charset;
    }

    function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function send()
    {
        $smtpException = null;
        $connection = null;
        try 
        {
            if (empty($this->from))
                throw new Exception("From cant be empty");
            if (count($this->recipients) == 0)
                throw new Exception("There be at least 1 recipient");
            if (empty($this->subject))
                throw new Exception("Subject cant be empty");
            if (empty($this->message))
                throw new Exception("Message cant be empty");
            
            $server = $this->server;
            if (strtolower(trim($this->secure)) == 'ssl')
                $server = 'ssl://' . $server;
            $connection = @fsockopen($server, $this->port, $errno, $errstr, 60);
            if ($connection == null || substr($this->getServerResponse($connection), 0, 3) != '220')
               throw new Exception("Error connecting to smtp server");

            fputs($connection, 'HELO ' . "localhost" . "\r\n");
            $this->getServerResponse($connection);
            if (strtolower(trim($this->secure)) == 'tls')
            {
                fputs($connection, 'STARTTLS' . "\r\n");
                if (substr($this->getServerResponse($connection), 0, 3) != '220')
                    throw new Exception("Error authenticating to smtp server");     
                stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fputs($connection, 'HELO ' . "localhost" . "\r\n");
                if (substr($this->getServerResponse($connection), 0, 3) != '250')
                    throw new Exception("Error authenticating to smtp server");     
            }
            if ($this->server != 'localhost')
            {
                fputs($connection, 'AUTH LOGIN' . "\r\n");
                if (substr($this->getServerResponse($connection), 0, 3) != '334')
                    throw new Exception("Error authenticating to smtp server");     
                fputs($connection, base64_encode($this->username) . "\r\n");
                if (substr($this->getServerResponse($connection), 0, 3) != '334')
                    throw new Exception("Error authenticating to smtp server");     
                fputs($connection, base64_encode($this->password) . "\r\n");
                if (substr($this->getServerResponse($connection), 0, 3) != '235')
                    throw new Exception("Error authenticating to smtp server");     
            }

            fputs($connection, 'MAIL FROM: <' . $this->getMailAddr($this->from) . '>' . "\r\n");
            $this->getServerResponse($connection);
            $allrecipients = array_merge($this->recipients, $this->cc, $this->bcc);
            foreach ($allrecipients as $recipient)
            {
                fputs($connection, 'RCPT TO: <' . $this->getMailAddr($recipient) . '>' . "\r\n");
                $this->getServerResponse($connection);
            }
            
            fputs($connection, 'DATA' . "\r\n");
            $this->getServerResponse($connection);
            $data = "Date: " . date("D, j M Y G:i:s") . " -0500" . "\r\n";
            $data .= "From: " . $this->from . "\r\n";
            $data .= "Reply-To: " . $this->from . "\r\n";
            $data .= "To: " . implode(",", $this->recipients) . "\r\n";
            if (count($this->cc) > 0)
                $data .= "CC: " . implode(",", $this->cc) . "\r\n";
            if (count($this->bcc) > 0)
                $data .= "BCC: " . implode(",", $this->bcc) . "\r\n";
            $data .= "Subject: " . $this->subject . "\r\n";
            $data .= "MIME-Version: 1.0" . "\r\n";
            if ($this->contentType == "multipart/mixed")
            {
                $boundary = $this->generateBoundary();
                $message = $this->multipartMessage($this->message, $boundary);
                $data .= "Content-Type: $this->contentType;" . "\r\n";
                $data .= "    boundary=\"$boundary\"";
            } 
            else
            {
                $data .= "Content-Type: $this->contentType; charset=\"" . $this->charset . "\"";
                $message = $this->message;
            }
            $data .= "\r\n" . "\r\n" . $message . "\r\n";
            $data .= "." . "\r\n";
            fputs($connection, $data);
            if (substr($this->getServerResponse($connection), 0, 3) != '250')
                throw new Exception ("SMTP rejected mail");
            
            fputs($connection, 'QUIT' . "\r\n");
        }
        catch (Exception $ex)
        {
            $smtpException = $ex;
        }
        if ($connection != null)
        {
            try { fclose($connection); } catch (Exception $exClose) {}
        }
        if ($smtpException != null)
            throw $smtpException;
    }

    private function getServerResponse($connection)
    {
        $data = "";
        while ($str = fgets($connection, 4096))
        {
            $data .= $str;
            if (substr($str, 3, 1) == " ")
            {
                break;
            }
        }
        return $data;
    }

    private function getMailAddr($emailaddr)
    {
        $addr = $emailaddr;
        $strSpace = strrpos($emailaddr, ' ');
        if ($strSpace > 0)
        {
            $addr = substr($emailaddr, $strSpace + 1);
            $addr = str_replace("<", "", $addr);
            $addr = str_replace(">", "", $addr);
        }
        return $addr;
    }

    private function randID($len)
    {
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $out = "";
        for ($t = 0; $t < $len; $t++)
        {
            $r = rand(0, 61);
            $out = $out . substr($index, $r, 1);
        }
        return $out;
    }

    private function generateBoundary()
    {
        $boundary = "--=_NextPart_000_";
        $boundary .= $this->randID(4) . "_";
        $boundary .= $this->randID(8) . ".";
        $boundary .= $this->randID(8);
        return $boundary;
    }

    private function multipartMessage($htmlpart, $boundary)
    {
        if ($this->altBody == "")
        {
            $this->altBody = $this->stripHtmlTags($htmlpart);
        }
        $altBoundary = $this->generateBoundary();
        ob_start();
        $parts = "This is a multi-part message in MIME format." . "\r\n" . "\r\n";
        $parts .= "--" . $boundary . "\r\n";
        $parts .= "Content-Type: multipart/alternative;" . "\r\n";
        $parts .= "    boundary=\"$altBoundary\"" . "\r\n" . "\r\n";
        $parts .= "--" . $altBoundary . "\r\n";
        $parts .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . "\r\n";
        $parts .= "Content-Transfer-Encoding: $this->transferEncodeing" . "\r\n" . "\r\n";
        $parts .= $this->altBody . "\r\n" . "\r\n";
        $parts .= "--" . $altBoundary . "\r\n";
        $parts .= "Content-Type: text/html; charset=\"" . $this->charset . "\"" . "\r\n";
        $parts .= "Content-Transfer-Encoding: $this->transferEncodeing" . "\r\n" . "\r\n";
        $parts .= $htmlpart . "\r\n" . "\r\n";
        $parts .= "--" . $altBoundary . "--" . "\r\n" . "\r\n";

        if (count($this->attachments) > 0)
        {
            for ($i = 0; $i < count($this->attachments); $i++)
            {
                $attachment = chunk_split(base64_encode(file_get_contents($this->attachments[$i])));
                $filename = basename($this->attachments[$i]);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $parts .= "--" . $boundary . "\r\n";
                $parts .= "Content-Type: application/$ext; name=\"$filename\"" . "\r\n";
                $parts .= "Content-Transfer-Encoding: base64" . "\r\n";
                $parts .= "Content-Disposition: attachment; filename=\"$filename\"" . "\r\n" . "\r\n";
                $parts .= $attachment . "\r\n";
            }
        }

        $parts .= "--" . $boundary . "--";
        $message = ob_get_clean();
        return $parts;
    }

    private function stripHtmlTags($text)
    {
        $text = preg_replace(array(
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
            '@<form[^>]*?.*?</form>@siu',
            '@<((br)|(hr))>@iu',
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ), array(
            " ", " ", " ", " ", " ", " ", " ", " ", " ", " ",
            " ", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ), $text);

        $text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
        $text = preg_replace("/\n( )*/", "\n", $text);
        return strip_tags($text);
    }
}

?>