<?php declare(strict_types=1);

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
namespace Kicaj\Test\Helper\Loader;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbItf;
use SplFileInfo;

/**
 * Class FixtureLoader.
 */
class FixtureLoader
{
    /**
     * Database connection.
     *
     * @var DbItf
     */
    private $db;

    /**
     * The root of fixture directory.
     *
     * @var string
     */
    private $fixturesRootPath;

    /**
     * Constructor.
     *
     * @param string $fixturesRootPath The path to fixture files root folder.
     * @param DbItf  $database         The database to load fixtures to.
     */
    public function __construct(string $fixturesRootPath, DbItf $database = null)
    {
        $this->fixturesRootPath = $fixturesRootPath;
        $this->db = $database;
    }

    /**
     * Return true if database has been set, false otherwise.
     *
     * @return bool
     */
    public function isDbSet(): bool
    {
        return (bool)$this->db;
    }

    /**
     * Load fixture to database.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @throws DatabaseEx
     * @throws FixtureLoaderEx
     */
    public function loadDbFixture(string $fixturePath)
    {
        list($fixtureFormat, $fixtureData) = $this->loadFixtureData($fixturePath);

        // Postpone database connection till we really need it.
        $this->db->dbConnect();

        $this->db->dbLoadFixture($fixtureFormat, $fixtureData);
    }

    /**
     * Load collection of fixtures.
     *
     * @param array $fixtureNames The array of fixture paths to load to database.
     *
     * @throws DatabaseEx
     * @throws FixtureLoaderEx
     */
    public function loadDbFixtures(array $fixtureNames)
    {
        foreach ($fixtureNames as $fixtureName) {
            $this->loadDbFixture($fixtureName);
        }
    }

    /**
     * Get fixture data.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @throws FixtureLoaderEx
     *
     * @return mixed
     */
    public function getFixtureData(string $fixturePath)
    {
        return $this->loadFixtureData($fixturePath)[1];
    }

    /**
     * Get fixture file contents.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @return string
     */
    public function getFixtureRawData(string $fixturePath)
    {
        $fixturePath = $this->fixturesRootPath . '/' . $fixturePath;

        return file_get_contents($fixturePath);
    }

    /**
     * Load fixture file from disk.
     *
     * @param string $fixturePath The path to fixture file.
     *
     * @throws FixtureLoaderEx
     *
     * @return array The array where index 0 holds fixture format and index 1 holds the fixture content.
     */
    public function loadFixtureData(string $fixturePath): array
    {
        $fixturePath = $this->fixturesRootPath . '/' . $fixturePath;
        $fixtureFormat = $this->detectFormat($fixturePath);

        $fixtureData = null;

        switch ($fixtureFormat) {
            case DbItf::FIXTURE_FORMAT_JSON:
                $fixtureData = $this->decode($this->loadJson($fixturePath));
                break;

            case DbItf::FIXTURE_FORMAT_TXT:
                $fixtureData = $this->loadTxt($fixturePath);
                break;

            case DbItf::FIXTURE_FORMAT_PHP:
                /** @noinspection PhpIncludeInspection */
                $fixtureData = require $fixturePath;
                break;

            case DbItf::FIXTURE_FORMAT_SQL:
                $fixtureData = $this->loadSql($fixturePath);
                break;
        }

        return [$fixtureFormat, $fixtureData];
    }

    /**
     * Detect fixture format based on its extension.
     *
     * @param string $fixturePath The path to fixture file.
     *
     * @throws FixtureLoaderEx
     *
     * @return string The one of self::FORMAT_* constants
     */
    public function detectFormat(string $fixturePath): string
    {
        $info = new SplFileInfo($fixturePath);
        $extension = $info->getExtension();
        $knownFormats = [
            DbItf::FIXTURE_FORMAT_JSON,
            DbItf::FIXTURE_FORMAT_PHP,
            DbItf::FIXTURE_FORMAT_SQL,
            DbItf::FIXTURE_FORMAT_TXT,
        ];

        if (!in_array($extension, $knownFormats)) {
            throw new FixtureLoaderEx("Unknown fixture format: $extension.");
        }

        return $extension;
    }

    /**
     * Load text fixture.
     *
     * @param string $fixturePath The fixture path.
     *
     * @throws FixtureLoaderEx
     *
     * @return string
     */
    public function loadTxt(string $fixturePath): string
    {
        if (!file_exists($fixturePath)) {
            throw new FixtureLoaderEx("Fixture $fixturePath does not exist.");
        }

        return file_get_contents($fixturePath);
    }

    /**
     * Load JSON fixture.
     *
     * @param string $fixturePath The fixture path.
     *
     * @throws FixtureLoaderEx
     *
     * @return string
     */
    public function loadJson(string $fixturePath): string
    {
        if (!file_exists($fixturePath)) {
            throw new FixtureLoaderEx("Fixture $fixturePath does not exist.");
        }

        $lines = file($fixturePath);
        $endLine = -1;
        foreach ($lines as $lineNo => $data) {
            if (substr($data, 0, 2) === '--') {
                $endLine = $lineNo;
            } else {
                break;
            }
        }

        if ($endLine != -1) {
            array_splice($lines, 0, $endLine + 1);
        }

        return implode("\n", $lines);
    }

    /**
     * Get array of SQL statements form fixture file.
     *
     * @param string $fixturePath The fixture path.
     *
     * @throws FixtureLoaderEx
     *
     * @return array
     */
    public function loadSql(string $fixturePath): array
    {
        if (!file_exists($fixturePath)) {
            throw new FixtureLoaderEx("Fixture $fixturePath does not exist.");
        }

        $handle = @fopen($fixturePath, 'r');

        if (!$handle) {
            throw new FixtureLoaderEx("Error opening fixture $fixturePath.");
        }

        $sqlArr = [];

        $index = 0;
        while (($sql = fgets($handle)) !== false) {
            // Skip comments
            if (substr($sql, 0, 2) == '--') {
                continue;
            }

            $isMultiLineSql = array_key_exists($index, $sqlArr);
            $isEndOfSql = substr($sql, -2, 1) == ';';

            if ($isMultiLineSql) {
                $sqlArr[$index] .= $sql;
            } else {
                $sqlArr[$index] = $sql;
            }

            if ($isEndOfSql) {
                $sqlArr[$index] = trim($sqlArr[$index]);
                ++$index;
            }
        }

        fclose($handle);

        return $sqlArr;
    }

    /**
     * Decode JSON string.
     *
     * @param string     $json    The JOSN string being decoded
     * @param bool|false $asClass Set to true to get stdClass
     * @param int        $depth   The user specified recursion depth
     * @param int        $options The bitmask of JSON decode options
     *
     * @return mixed
     *
     * @throws FixtureLoaderEx If passed $json string is not JSON
     */
    public function decode(string $json, bool $asClass = false, int $depth = 512, int $options = 0)
    {
        $result = json_decode($json, !$asClass, $depth, $options);

        $le = json_last_error();
        switch ($le) {
            case JSON_ERROR_NONE:
                return $result;
            case JSON_ERROR_DEPTH:
                throw new FixtureLoaderEx('Maximum stack depth exceeded', $le);
            case JSON_ERROR_SYNTAX:
            case JSON_ERROR_STATE_MISMATCH:
            case JSON_ERROR_CTRL_CHAR:
            case JSON_ERROR_UTF8:
                throw new FixtureLoaderEx('JSON decoding error', $le);
            default:
                throw new FixtureLoaderEx('Unknown error ' . $le);
        }
    }
}
