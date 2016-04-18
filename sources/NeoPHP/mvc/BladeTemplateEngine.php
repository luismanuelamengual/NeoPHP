<?php

namespace NeoPHP\mvc;

use Closure;

class BladeTemplateEngine extends TemplateEngine
{
    protected $contentTags = ['{{','}}'];
    protected $escapedTags = ['{{{','}}}'];
    protected $extensions = [];
    protected $compilers = array(
        'Extensions',
        'Extends',
        'Comments',
        'Echos',
        'Openings',
        'Closings',
        'Else',
        'Unless',
        'EndUnless',
        'Includes',
        'Each',
        'Yields',
        'Shows',
        'Uses',
        'Translations',
        'SectionStart',
        'SectionStop',
        'SectionOverwrite'
    );
    
    public function compile($content)
    {
        return $this->compileString($content);
    }
    
    protected function compileString($value) 
    {
        foreach ($this->compilers as $compiler)
        {
            $value = $this->{"compile{$compiler}"}($value);
        }
        return $value;
    }
    
    public function extend(Closure $compiler)
    {
        $this->extensions[] = $compiler;
    }

    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler)
        {
            $value = call_user_func($compiler, $value, $this);
        }
        return $value;
    }
	
    protected function compileExtends($value)
    {
        if (strpos($value, '@extends') !== 0)
        {
            return $value;
        }
        $lines = preg_split("/(\r?\n)/", $value);
        $lines = $this->compileLayoutExtends($lines);
        return implode("\r\n", array_slice($lines, 1));
    }
	
    protected function compileLayoutExtends($lines)
    {
        $pattern = $this->createMatcher('extends');
        $lines[] = preg_replace($pattern, '$1@include$2', $lines[0]);
        return $lines;
    }
	
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);
        return preg_replace($pattern, '<?php /* $1 */ ?>', $value);
    }
    
    protected function compileEchos($value)
    {
        $difference = strlen($this->contentTags[0]) - strlen($this->escapedTags[0]);

        if ($difference > 0)
        {
            return $this->compileEscapedEchos($this->compileRegularEchos($value));
        }
        return $this->compileRegularEchos($this->compileEscapedEchos($value));
    }
    
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/%s\s*(.+?)\s*%s/s', $this->contentTags[0], $this->contentTags[1]);
        return preg_replace($pattern, '<?php echo $1; ?>', $value);
    }
	
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/%s\s*(.+?)\s*%s/s', $this->escapedTags[0], $this->escapedTags[1]);
        return preg_replace($pattern, '<?php echo e($1); ?>', $value);
    }
	
    protected function compileOpenings($value)
    {
        $pattern = '/(?(R)\((?:[^\(\)]|(?R))*\)|(?<!\w)(\s*)@(if|elseif|foreach|for|while)(\s*(?R)+))/';
        return preg_replace($pattern, '$1<?php $2$3: ?>', $value);
    }
    
    protected function compileClosings($value)
    {
        $pattern = '/(\s*)@(endif|endforeach|endfor|endwhile)(\s*)/';
        return preg_replace($pattern, '$1<?php $2; ?>$3', $value);
    }
	
    protected function compileElse($value)
    {
        $pattern = $this->createPlainMatcher('else');
        return preg_replace($pattern, '$1<?php else: ?>$2', $value);
    }
	
    protected function compileUnless($value)
    {
        $pattern = $this->createMatcher('unless');
        return preg_replace($pattern, '$1<?php if ( !$2): ?>', $value);
    }
	
    protected function compileEndUnless($value)
    {
        $pattern = $this->createPlainMatcher('endunless');
        return preg_replace($pattern, '$1<?php endif; ?>$2', $value);
    }
	
    protected function compileIncludes($value)
    {
        $pattern = $this->createMatcher('include');
        $replace = '$1<?php $this->includeTemplate$2; ?>';
        return preg_replace($pattern, $replace, $value);
    }
	
    protected function compileEach($value)
    {
        $pattern = $this->createMatcher('each');
        return preg_replace($pattern, '$1<?php echo $this->renderEach$2; ?>', $value);
    }
	
    protected function compileYields($value)
    {
        $pattern = $this->createMatcher('yield');
        return preg_replace($pattern, '$1<?php echo $this->yieldContent$2; ?>', $value);
    }
	
    protected function compileShows($value)
    {
        $pattern = $this->createPlainMatcher('show');
        return preg_replace($pattern, '$1<?php echo $this->yieldSection(); ?>$2', $value);
    }
	
    protected function compileUses($value)
    {
        $pattern = $this->createMatcher('use');
        return preg_replace_callback($pattern, function ($match) 
        {
            list($fullmatch, $beforeContent, $afterContent) = $match; 
            $useParametersString = trim($afterContent);
            $useParametersString = substr($useParametersString, 1, -1);
            $useParameters = explode(",", $useParametersString);
            $replacementValue = "<?php use ";
            $replacementValue .= substr($useParameters[0], 1, -1);
            if (isset($useParameters[1]))
            {
                $replacementValue .= " as ";
                $replacementValue .= substr($useParameters[1], 1, -1);
            }
            $replacementValue .= "; ?>";
            return $replacementValue;
        }, $value);
    }
    
    protected function compileTranslations($value)
    {
        $pattern = $this->createMatcher('lang');
        return preg_replace($pattern, '$1<?php echo $this->getText$2; ?>', $value);
    }
	
    protected function compileSectionStart($value)
    {
        $pattern = $this->createMatcher('section');
        return preg_replace($pattern, '$1<?php $this->startSection$2; ?>', $value);
    }
	
    protected function compileSectionStop($value)
    {
        $pattern = $this->createPlainMatcher('stop');
        $value = preg_replace($pattern, '$1<?php $this->stopSection(); ?>$2', $value);
        $pattern = $this->createPlainMatcher('endsection');
        return preg_replace($pattern, '$1<?php $this->stopSection(); ?>$2', $value);
    }
	
    protected function compileSectionOverwrite($value)
    {
        $pattern = $this->createPlainMatcher('overwrite');
        return preg_replace($pattern, '$1<?php $this->stopSection(true); ?>$2', $value);
    }
	
    protected function createMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*\))/';
    }
	
    protected function createOpenMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*)\)/';
    }
	
    protected function createPlainMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*)/';
    }
	
    public function setContentTags($openTag, $closeTag, $escaped = false)
    {
        $property = ($escaped === true) ? 'escapedTags' : 'contentTags';
        $this->{$property} = array(preg_quote($openTag), preg_quote($closeTag));
    }
	
    public function setEscapedContentTags($openTag, $closeTag)
    {
        $this->setContentTags($openTag, $closeTag, true);
    }
}