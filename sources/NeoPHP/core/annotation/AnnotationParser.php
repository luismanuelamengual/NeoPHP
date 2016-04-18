<?php

namespace NeoPHP\core\annotation;

abstract class AnnotationParser
{
    public static function getAnnotations ($comment)
    {
        $annotations = array();
        if (preg_match_all("/@(?=(.*)[ ]*(?:@|\r\n|\n))/U", $comment, $matches))
        {
            foreach ($matches[1] as $rawParameter)
            {
                $paramterStartPos = strpos($rawParameter, "(");
                if ($paramterStartPos === false)
                {
                    $annotations[] = new Annotation($rawParameter);
                }
                else
                {
                    $annotationName = trim(substr($rawParameter, 0, $paramterStartPos));
                    $parameterEndPos = strrpos($rawParameter, ")");
                    $annotationParametersString = substr($rawParameter, $paramterStartPos+1, $parameterEndPos-$paramterStartPos-1);
                    preg_match_all("/\"[^\"]*\"|'[^']*'/", $annotationParametersString, $matches);
                    $annotationStrings = array();
                    $annotationStringReplacement = "@@@";
                    if (isset($matches[0]) && is_array($matches[0]))
                    {   
                        $annotationParametersString = preg_replace("/\"[^\"]*\"|'[^']*'/", $annotationStringReplacement, $annotationParametersString);
                        foreach ($matches[0] as $string)
                        {
                            $string = trim($string, '"');
                            $string = trim($string, '\'');
                            $annotationStrings[] = $string;
                        }
                    }
                    
                    $annotationStringIndex = 0;
                    $annotationParameterTokens = explode(",", $annotationParametersString);
                    $annotationParameters = array();
                    foreach ($annotationParameterTokens as $annotationParameterToken)
                    {
                        $propertyTokens = explode("=", $annotationParameterToken);
                        if (sizeof($propertyTokens) > 1)
                        {
                            $key = trim($propertyTokens[0]);
                            $value = trim($propertyTokens[1]);
                        }
                        else
                        {
                            $key = null;
                            $value = trim($propertyTokens[0]);
                        }
                        
                        if ($value == $annotationStringReplacement)
                            $value = '"' . $annotationStrings[$annotationStringIndex++] . '"';
                        $value = @eval("return " . $value . ";");
                        
                        if ($key != null)
                        {
                            $annotationParameters[$key] = $value;
                        }
                        else
                        {
                            $annotationParameters[] = $value;
                        }
                    }
                    $annotations[] = new Annotation($annotationName, $annotationParameters);
                }          
            }
        }
        return $annotations;
    }
}

?>
