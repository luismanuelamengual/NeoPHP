<?php

namespace NeoPHP\Views\Jinx;

use Exception;
use NeoPHP\Views\View;
use RuntimeException;

class JinxView extends View {

    protected $templatesPath;
    protected $compiledTemplatesPath;
    protected $sections;
    protected $sectionsStack;
    protected $contentTags = ['{{', '}}'];
    protected $escapedTags = ['{{{', '}}}'];
    protected $extensions = [];
    protected $compilers = [
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
    ];

    public function __construct($templatesPath, $compiledTemplatesPath, $name, array $parameters = []) {
        parent::__construct($name, $parameters);
        $this->templatesPath = $templatesPath;
        $this->compiledTemplatesPath = $compiledTemplatesPath;
        $this->sections = [];
        $this->sectionsStack = [];
    }

    private function compile($content) {
        return $this->compileString($content);
    }

    private function compileString($value) {
        foreach ($this->compilers as $compiler) {
            $value = $this->{"compile{$compiler}"}($value);
        }
        return $value;
    }

    private function extend(Closure $compiler) {
        $this->extensions[] = $compiler;
    }

    private function compileExtensions($value) {
        foreach ($this->extensions as $compiler) {
            $value = call_user_func($compiler, $value, $this);
        }
        return $value;
    }

    private function compileExtends($value) {
        if (strpos($value, '@extends') !== 0) {
            return $value;
        }
        $lines = preg_split("/(\r?\n)/", $value);
        $lines = $this->compileLayoutExtends($lines);
        return implode("\r\n", array_slice($lines, 1));
    }

    private function compileLayoutExtends($lines) {
        $pattern = $this->createMatcher('extends');
        $lines[] = preg_replace($pattern, '$1@include$2', $lines[0]);
        return $lines;
    }

    private function compileComments($value) {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);
        return preg_replace($pattern, '<?php /* $1 */ ?>', $value);
    }

    private function compileEchos($value) {
        $difference = strlen($this->contentTags[0]) - strlen($this->escapedTags[0]);

        if ($difference > 0) {
            return $this->compileEscapedEchos($this->compileRegularEchos($value));
        }
        return $this->compileRegularEchos($this->compileEscapedEchos($value));
    }

    private function compileRegularEchos($value) {
        $pattern = sprintf('/%s\s*(.+?)\s*%s/s', $this->contentTags[0], $this->contentTags[1]);
        return preg_replace($pattern, '<?php echo $1; ?>', $value);
    }

    private function compileEscapedEchos($value) {
        $pattern = sprintf('/%s\s*(.+?)\s*%s/s', $this->escapedTags[0], $this->escapedTags[1]);
        return preg_replace($pattern, '<?php echo e($1); ?>', $value);
    }

    private function compileOpenings($value) {
        $pattern = '/(?(R)\((?:[^\(\)]|(?R))*\)|(?<!\w)(\s*)@(if|elseif|foreach|for|while)(\s*(?R)+))/';
        return preg_replace($pattern, '$1<?php $2$3: ?>', $value);
    }

    private function compileClosings($value) {
        $pattern = '/(\s*)@(endif|endforeach|endfor|endwhile)(\s*)/';
        return preg_replace($pattern, '$1<?php $2; ?>$3', $value);
    }

    private function compileElse($value) {
        $pattern = $this->createPlainMatcher('else');
        return preg_replace($pattern, '$1<?php else: ?>$2', $value);
    }

    private function compileUnless($value) {
        $pattern = $this->createMatcher('unless');
        return preg_replace($pattern, '$1<?php if ( !$2): ?>', $value);
    }

    private function compileEndUnless($value) {
        $pattern = $this->createPlainMatcher('endunless');
        return preg_replace($pattern, '$1<?php endif; ?>$2', $value);
    }

    private function compileIncludes($value) {
        $pattern = $this->createMatcher('include');
        $replace = '$1<?php $this->includeTemplate$2; ?>';
        return preg_replace($pattern, $replace, $value);
    }

    private function compileEach($value) {
        $pattern = $this->createMatcher('each');
        return preg_replace($pattern, '$1<?php echo $this->renderEach$2; ?>', $value);
    }

    private function compileYields($value) {
        $pattern = $this->createMatcher('yield');
        return preg_replace($pattern, '$1<?php echo $this->yieldContent$2; ?>', $value);
    }

    private function compileShows($value) {
        $pattern = $this->createPlainMatcher('show');
        return preg_replace($pattern, '$1<?php echo $this->yieldSection(); ?>$2', $value);
    }

    private function compileUses($value) {
        $pattern = $this->createMatcher('use');
        return preg_replace_callback($pattern, function ($match) {
            list($fullmatch, $beforeContent, $afterContent) = $match;
            $useParametersString = trim($afterContent);
            $useParametersString = substr($useParametersString, 1, -1);
            $useParameters = explode(",", $useParametersString);
            $replacementValue = "<?php use ";
            $replacementValue .= substr($useParameters[0], 1, -1);
            if (isset($useParameters[1])) {
                $replacementValue .= " as ";
                $replacementValue .= substr($useParameters[1], 1, -1);
            }
            $replacementValue .= "; ?>";
            return $replacementValue;
        }, $value);
    }

    private function compileTranslations($value) {
        $pattern = $this->createMatcher('lang');
        return preg_replace($pattern, '$1<?php echo $this->getText$2; ?>', $value);
    }

    private function compileSectionStart($value) {
        $pattern = $this->createMatcher('section');
        return preg_replace($pattern, '$1<?php $this->startSection$2; ?>', $value);
    }

    private function compileSectionStop($value) {
        $pattern = $this->createPlainMatcher('stop');
        $value = preg_replace($pattern, '$1<?php $this->stopSection(); ?>$2', $value);
        $pattern = $this->createPlainMatcher('endsection');
        return preg_replace($pattern, '$1<?php $this->stopSection(); ?>$2', $value);
    }

    private function compileSectionOverwrite($value) {
        $pattern = $this->createPlainMatcher('overwrite');
        return preg_replace($pattern, '$1<?php $this->stopSection(true); ?>$2', $value);
    }

    private function createMatcher($function) {
        return '/(?<!\w)(\s*)@' . $function . '(\s*\(.*\))/';
    }

    private function createOpenMatcher($function) {
        return '/(?<!\w)(\s*)@' . $function . '(\s*\(.*)\)/';
    }

    private function createPlainMatcher($function) {
        return '/(?<!\w)(\s*)@' . $function . '(\s*)/';
    }

    private function setContentTags($openTag, $closeTag, $escaped = false) {
        $property = ($escaped === true) ? 'escapedTags' : 'contentTags';
        $this->{$property} = array(preg_quote($openTag), preg_quote($closeTag));
    }

    private function setEscapedContentTags($openTag, $closeTag) {
        $this->setContentTags($openTag, $closeTag, true);
    }

    private final function startSection($sectionName, $sectionContent = null) {
        if (!empty($sectionContent)) {
            $this->extendsection($sectionName, $sectionContent);
        }
        else {
            ob_start() and $this->sectionsStack[] = $sectionName;
        }
    }

    private final function stopSection($overrite = false) {
        $sectionName = array_pop($this->sectionsStack);
        $sectionContent = ob_get_clean();
        if ($overrite) {
            $this->sections[$sectionName] = $sectionContent;
        }
        else {
            $this->extendSection($sectionName, $sectionContent);
        }
        return $sectionName;
    }

    private final function extendSection($sectionName, $sectionContent) {
        if (isset($this->sections[$sectionName])) {
            $sectionContent = str_replace('@parent', $sectionContent, $this->sections[$sectionName]);
        }
        $this->sections[$sectionName] = $sectionContent;
    }

    private final function yieldSection() {
        return $this->yieldContent($this->stopSection());
    }

    private final function yieldContent($sectionName, $defaultSectionContent = null) {
        return isset($this->sections[$sectionName]) ? $this->sections[$sectionName] : $defaultSectionContent;
    }

    private final function include($templateName, array $parameters = []) {
        $templateFilename = null;
        $templateParameters = array_merge($this->parameters, $parameters);
        extract($templateParameters);

        $templateFilename = $this->templatesPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".jx";
        if (!file_exists($templateFilename)) {
            throw new RuntimeException("Template file \"$templateFilename\" not found !!");
        }
        $compiledTemplateFilename = $this->compiledTemplatesPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".php";
        if (!file_exists($compiledTemplateFilename) || (filemtime($templateFilename) > filemtime($compiledTemplateFilename))) {
            $contents = file_get_contents($templateFilename);
            $compiledContents = $this->compile($contents);
            try {
                $compiledTemplatesDirectory = dirname($compiledTemplateFilename);
                if (!file_exists($compiledTemplatesDirectory)) {
                    mkdir($compiledTemplatesDirectory, 0777, true);
                }
                file_put_contents($compiledTemplateFilename, $compiledContents);
            }
            catch (Exception $ex) {
                throw new RuntimeException("Permission denied to create or update template cache file \"$compiledTemplateFilename\"");
            }
        }
        @include $compiledTemplateFilename;
    }

    protected function renderContent() {
        $this->include($this->name);
    }
}