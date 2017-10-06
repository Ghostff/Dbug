<?php

/**
 * Bittr
 *
 * @license
 *
 * New BSD License
 *
 * Copyright (c) 2017, ghostff community
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *      1. Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *      2. Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *      3. All advertising materials mentioning features or use of this software
 *      must display the following acknowledgement:
 *      This product includes software developed by the ghostff.
 *      4. Neither the name of the ghostff nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY ghostff ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL GHOSTFF COMMUNITY BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

declare(strict_types=1);

namespace Dbug;

class MyThrowable
{
    protected $message = null;
    protected $code = null;
    protected $file = null;
    protected $line = null;
    protected $trace = [];
    protected $trace_string = null;


    /*
     * This class is made to serve as an alternative to
     *      new BittrDbug(function (Throwable $e) { ... });
     * Which has been removed. This can be done instead
     *      new BittrDbug(function (MyThrowable $e) { ... });
     * Though the getPrevious()
     */
    public function __construct(string $message, string $file, int $line, string $type, array $trace, int $code, string $trace_string)
    {
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->trace = $trace;
        $this->code = $code;
        $this->trace_string = $trace_string;
    }

    final public function getMessage(): string
    {
        return $this->message;
    }

    final public function getCode(): int
    {
        return $this->code;
    }

    final public function getFile(): string
    {
        return $this->file;
    }

    final public function getLine(): int
    {
        return $this->line;
    }

    final public function getTrace(): array
    {
        return $this->trace;
    }

    final public function getTraceAsString(): string
    {
        return $this->trace_string;
    }

    public function __toString(): string
    {
        return print_r($this,true);
    }
}
