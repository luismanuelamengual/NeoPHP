<?php

namespace NeoPHP\FileSystem;

class PrintStream extends FilterOutputStream {

    public function printb($buffer) {
        $this->out->write($buffer);
    }

    public function println($buffer = "") {
        $this->out->write($buffer . PHP_EOL);
    }

    public function printf() {
        $arguments = func_get_args();
        $format = array_splice($arguments, 0, 1);
        $this->out->write(vsprintf($format, $arguments));
    }
}