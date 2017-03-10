<?php

/**
 * Copyright 2015 Rafal Zajac <rzajac@gmail.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace Kicaj\Test\Helper\Server;

use Exception;

/**
 * Manages PHP build in web server.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class HttpServer
{
    /**
     * The absolute path to document root directory.
     *
     * @var string
     */
    private $docRoot;

    /**
     * The port to start server on.
     *
     * @var int
     */
    private $port;

    /**
     * The host or IP to start server on.
     *
     * @var string
     */
    private $host;

    /**
     * The pid of the started server.
     *
     * @var
     */
    private $pid;

    /**
     * Constructor.
     *
     * @param string $docRoot The absolute path to document root directory.
     * @param int    $port    The port to start server on.
     * @param string $host    The host or IP to start server on.
     */
    public function __construct($docRoot, $port = null, $host = '127.0.0.1')
    {
        $this->docRoot = $docRoot;
        $this->port = $port ?: rand(9000, 10000);
        $this->host = $host;
    }

    /**
     * Return server port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get server URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return 'http://' . $this->host . ':' . $this->port;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Start web server.
     *
     * @throws Exception
     */
    public function start()
    {
        $cmd = $this->getStartCmd();

        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            throw new Exception('Error staring build-in server.');
        }

        $this->pid = (int)$output[0];
        usleep(100000); // We need to allow some time for server to start.

        return $this->pid;
    }

    /**
     * Stop web server.
     *
     * @throws Exception
     */
    public function stop()
    {
        if ($this->pid) {
            exec('kill ' . $this->pid, $output, $ret);
            if ($ret !== 0) {
                throw new Exception('Error stopping build-in server.');
            }
            $this->pid = null;
        }
    }

    /**
     * Get command for starting build in HTTP server.
     *
     * @return string
     */
    private function getStartCmd()
    {
        $cmdFormat = 'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!';

        return sprintf($cmdFormat, $this->host, $this->port, $this->docRoot);
    }
}
