<?php

class ar_core_phpunitResultPrinter implements PHPUnit_Framework_TestListener
{

    const EVENT_TEST_START      = 0;
    const EVENT_TEST_END        = 1;
    const EVENT_TESTSUITE_START = 2;
    const EVENT_TESTSUITE_END   = 3;

    protected $lastTestFailed = false;
    protected $numAssertions  = 0;
    protected $numTests       = 0;
    protected $numTestsRun    = 0;
    protected $scope          = [];

    public function printResult($result)
    {
        $currentObject = ar::context()->getObject();
        $this->scope[$currentObject->id] = $currentObject;
        $this->printHeader();
        $this->printErrors($result);
        $this->printWarnings($result);
        $this->printFailures($result);
        $this->printFooter($result);
    }

    protected function printErrors($result)
    {
        $this->printDefects($result->errors(), 'error');
    }

    protected function printFailures($result)
    {
        $this->printDefects($result->failures(), 'failure');
    }

    protected function printWarnings($result)
    {
        $this->printDefects($result->warnings(), 'warning');
    }

    protected function printDefects($defects, $type)
    {
        if ( isset($defects) && is_array($defects)) {
            $count = count($defects);
        }
        if ( $count == 0 ) {
            return;
        }
        $this->write("\n<div class=\"unity-defects-$type\">");
        $this->write(
            sprintf(
                "<div class=\"unity-defects-header\">There %s %d %s%s:</div>\n",
                ($count==1) ? 'was' : 'were',
                $count,
                $type,
                ($count==1) ? '' : 's'
            )
        );
        $this->write("\n<ol>\n");
        foreach ($defects as $defect) {
            $this->printDefect($defect);
        }
        $this->write("</ol></div>\n");
    }

    protected function printDefect($defect)
    {
        $this->write("\n\t<li>".$defect->getTestName()."<br>\n");
        $e = $defect->thrownException();
        $this->write("<div class=\"unity-defect-trace\">");
        $this->write("<div class=\"unity-defect-message\">");
        $this->write($e->getMessage());
        if ($e instanceof PHPUnit_Framework_ExpectationFailedException && $e->getComparisonFailure()) {
            $this->write('<pre>'.$e->getComparisonFailure()->getDiff().'</pre>');
        }
        $p = $e->getPrevious();
        while ($p) {
            $this->write($p->getMessage());
            $p = $p->getPrevious();
        }
        $this->printDefectTrace($e->getTrace());
        $this->write("</div>");
        $this->write("</div>");
        $this->write("\n\t</li>\n");
    }

    protected function printDefectTrace($trace)
    {
        global $store_config;
        $templatesRoot = $store_config['files'].'templates';
        $trace = array_filter($trace, function($t) use($templatesRoot) {
            return strpos($t['file'], $templatesRoot)===0;
        });
        foreach($trace as $entry) {
            $this->printTraceEntry($entry);
        }
    }

    protected function printTraceEntry($entry)
    {
        global $store_config;
        $templatesRoot = $store_config['files'].'templates';
        $file          = substr($entry['file'], strlen($templatesRoot)+1);
        $template      = substr($file, strrpos($file, '/')+2, -4);
        $idPath        = substr($file, 0, -(strlen($template)+6));
        $id            = $this->getTemplateId($idPath);
        $language      = substr($template, strrpos($template, '.')+1);
        $subtype       = substr($template, 0, strpos($template, '.', 2));
        $type          = substr($template, 0, strpos($template, '.'));
        if (!isset($this->scope[$id])) {
            $this->scope[$id] = current(ar::get('/')->find('object.id='.$id)->call('system.get.phtml'));
        }
        $object = $this->scope[$id];
        if (!isset($object->config->pinp[$subtype])) {
            $subtype = $type;
        }
        $template = substr($template, strlen($subtype)+1, -(strlen($language)+1));
        $path = $this->scope[$id]->path;
        $heleneTemplate = $path.$subtype.'::'.$template.'.'.$language;
        $options = [];
        if ( !$object->data->config->templates[$subtype][$template][$language] ) {
            $options[] = 'local';
        }
        if ( $object->data->config->privatetemplates[$subtype][$template] ) {
            $options[] = 'private';
        }
        if (count($options)) {
            $heleneTemplate.='['.join(',',$options).']';
        }
        $this->write("<div class=\"unity-trace-line\">");
        if ( ar_pinp::exists('helene.template.html') ) {
            $closeLink = true;
            $this->write("<a target=\"_blank\" href=\"helene.template.html?heleneTemplate=".RawURLEncode($heleneTemplate)."\">");
        }
        $this->write("<span class=\"unity-trace-path\" title=\"$path\">$path</span>:<span class=\"unity-trace-type\">$type</span>::<span class=\"unity-trace-template\">$template</span>");
        if ($language!='any') {
            $this->write("(<span class=\"unity-trace-language\">$language</span>)");
        }
        if ($closeLink) {
            $this->write("</a>");
        }
        $this->write(" line <span class=\"unity-trace-line-nr\">{$entry['line']}</span>");
        $this->write("</div>");
    }

    protected function getTemplateId($idPath)
    {
        $parts = explode('/',$idPath);
        $id = 0;
        $multiplier = 1;
        foreach($parts as $part) {
            $id += ($multiplier * (int)$part);
            $multiplier = $multiplier*100;
        }
        return $id;
    }

    protected function printHeader()
    {
        $this->write("<div class=\"unity-result\"><header>".PHP_Timer::resourceUsage()."</header>");
    }

    protected function printFooter($result)
    {
        $this->write("<foot>");
        if ( !$result || count($result)===0) {
            $this->write("<div class=\"unity-warning\">No tests executed!</div>");
        } else if ( $result->wasSuccessful() &&
            $result->allHarmless() &&
            $result->allCompletelyImplemented() &&
            $result->noneSkipped()
        ) {
            $this->write(sprintf(
                "<div class=\"unity-success\">OK (%d test%s, %d assertion%s)</div>",
                count($result),
                (count($result)==1) ? '' : 's',
                $this->numAssertions,
                ($this->numAssertions==1) ? '' : 's'
            ));
        } else {
            if ( $result->wasSuccessful() ) {
                $this->write("<div class=\"unity-warning\">OK, but incomplete, skipped or risky tests!</div>");
            } else {
                $this->showFailureCount($result);
            }
        }
        $this->write("</foot></div>");
    }

    protected function showFailureCount($result)
    {
        $showFailures = (
            $result->errors() ||
            $result->failures() ||
            $result->skipped() ||
            $result->notImplemented() ||
            $result->risky()
        ) &&
            !$result->warnings()
        ;
        if ($showFailures) {
            $text  = 'FAILURES!';
            $class = 'unity-failure';
        } else {
            $text  = 'WARNINGS!';
            $class = 'unity-warning';
        }
        $this->write("<div class=\"unity-count $class\">$text ");
        $this->write(sprintf(
            "<span class=\"unity-count-tests\">%d Tests</span> ",
            count($result)
        ));
        $this->write(sprintf(
            "<span class=\"unity-count-assertions\">%d Assertions</span> ",
            $this->numAssertions
        ));
        if ($result->errorCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-errors\">%d Errors</span> ",
                $result->errorCount()
            ));
        }
        if ($result->failureCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-failures\">%d Failures</span> ",
                $result->failureCount()
            ));
        }
        if ($result->warningCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-warnings\">%d Warnings</span> ",
                $result->warningCount()
            ));
        }
        if ($result->skippedCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-skipped\">%d Skipped</span> ",
                $result->skippedCount()
            ));
        }
        if ($result->notImplementedCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-incomplete\">%d Incomplete</span> ",
                $result->notImplementedCount()
            ));
        }
        if ($result->riskyCount()) {
            $this->write(sprintf(
                "<span class=\"unity-count-risky\">%d Risky</span> ",
                $result->riskyCount()
            ));
        }
        $this->write("<div>");
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress('E', 'unity-error');
        $this->lastTestFailed = true;
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeProgress('F', 'unity-failure');
        $this->lastTestFailed = true;
    }

    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        $this->writeProgress('W', 'unity-warning');
        $this->lastTestFailed = true;
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress('I', 'unity-incomplete');
        $this->lastTestFailed = true;
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress('R', 'unity-risky');
        $this->lastTestFailed = true;
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress('S', 'unity-skipped');
        $this->lastTestFailed = true;
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->numTests==-1) {
            $this->numTests = count($suite);
        }
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->lastTestFailed) {
            $this->writeProgress('.');
        }
        $this->numAssertions += $test->getNumAssertions();
        $this->lastTestFailed = false;
    }

    public function write($s)
    {
        echo $s;
    }

    public function writeProgress($c, $class="")
    {
        $this->write("<span class=\"$class\">$c</span>\n");
        flush();
    }
}