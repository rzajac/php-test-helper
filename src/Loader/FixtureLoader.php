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
namespace Kicaj\Test\Helper\Loader;

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Tools\Api\JSON;
use SplFileInfo;

/**
 * Class FixtureLoader.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
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
    public function __construct($fixturesRootPath, DbItf $database)
    {
        $this->fixturesRootPath = $fixturesRootPath;
        $this->db               = $database;
    }

    /**
     * Load fixture to database.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @throws \Exception
     * @throws null Somehow PhpStorm needs it ???
     */
    public function loadDbFixture($fixturePath)
    {
        list($fixtureFormat, $fixtureData) = $this->loadFixtureData($fixturePath);

        // Postpone database connection till we really need it.
        if (!$this->db->dbConnect()) {
            throw $this->db->getError();
        }

        $this->db->dbLoadFixture($fixtureFormat, $fixtureData);
    }

    /**
     * Load collection of fixtures.
     *
     * @param array $fixtureNames The array of fixture paths to load to database.
     *
     * @throws \Exception
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
     * @return mixed
     */
    public function getFixtureData($fixturePath)
    {
        return $this->loadFixtureData($fixturePath)[1];
    }

    /**
     * Load fixture file from disk.
     *
     * @param string $fixturePath The path to fixture file.
     *
     * @throws \Exception
     *
     * @return array The array where index 0 holds fixture format and index 1 holds the fixture content.
     */
    public function loadFixtureData($fixturePath)
    {
        $fixturePath   = $this->fixturesRootPath . '/' . $fixturePath;
        $fixtureFormat = $this->detectFormat($fixturePath);

        $fixtureData = null;

        switch ($fixtureFormat) {
            case DbItf::FIXTURE_FORMAT_JSON:
                $fixtureData = JSON::decode(file_get_contents($fixturePath));
                break;

            case DbItf::FIXTURE_FORMAT_TXT:
                $fixtureData = file_get_contents($fixturePath);
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
     * @throws \Exception
     *
     * @return string The one of self::FORMAT_* constants
     */
    public function detectFormat($fixturePath)
    {
        $info         = new SplFileInfo($fixturePath);
        $extension    = $info->getExtension();
        $knownFormats = [
            DbItf::FIXTURE_FORMAT_JSON,
            DbItf::FIXTURE_FORMAT_PHP,
            DbItf::FIXTURE_FORMAT_SQL,
            DbItf::FIXTURE_FORMAT_TXT,
        ];

        if (!in_array($extension, $knownFormats)) {
            throw new \Exception("Unknown fixture format: $extension.");
        }

        return $extension;
    }

    /**
     * Get array of SQL statements form fixture file.
     *
     * @param string $fixturePath The fixture path.
     *
     * @throws \Exception
     *
     * @return array
     */
    private function loadSql($fixturePath)
    {
        if (!file_exists($fixturePath)) {
            throw new \Exception("Fixture $fixturePath does not exist.");
        }

        $handle = @fopen($fixturePath, 'r');

        if (!$handle) {
            throw new \Exception("Error opening fixture $fixturePath.");
        }

        $sqlArr = [];

        $index = 0;
        while (($sql = fgets($handle)) !== false) {
            // Skip comments
            if (substr($sql, 0, 2) == '--') {
                continue;
            }

            $isMultiLineSql = array_key_exists($index, $sqlArr);
            $isEndOfSql     = substr($sql, -2, 1) == ';';

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
}
