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
use Kicaj\Tools\Exception;
use SplFileInfo;

/**
 * Class FixtureLoader.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureLoader
{
    /** JSON format */
    const FORMAT_JSON = 'json';

    /** SQL format */
    const FORMAT_SQL = 'sql';

    /** TXT format */
    const FORMAT_TXT = 'txt';

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
     * @param DbItf|null $database         The database connection
     * @param string     $fixturesRootPath The path to fixture files
     */
    public function __construct($database, $fixturesRootPath)
    {
        $this->db = $database;
        $this->fixturesRootPath = $fixturesRootPath;
    }

    /**
     * Set database.
     *
     * @param DbItf $db
     *
     * @throws Exception
     *
     * @return FixtureLoader
     */
    public function setDb($db)
    {
        if ($this->db) {
            throw new Exception('cannot set database twice');
        }

        $this->db = $db;

        return $this;
    }

    /**
     * Load fixture to database.
     *
     * @param string $fixtureName The fixture path relative to fixturesRootPath
     *
     * @throws Exception
     */
    public function dbLoadFixture($fixtureName)
    {
        $fixtureData = $this->loadFileFixture($fixtureName);

        // Postpone database connection till we really need it.
        if (!$this->db->dbConnect()) {
            throw new Exception($this->db->getError()->getMessage());
        }

        $this->db->dbRunQuery($fixtureData);
    }

    /**
     * Load collection of fixtures.
     *
     * @param array $fixtureNames
     *
     * @throws Exception
     */
    public function dbLoadFixtures(array $fixtureNames)
    {
        foreach ($fixtureNames as $fixtureName) {
            $this->dbLoadFixture($fixtureName);
        }
    }

    /**
     * Load fixture file from disk.
     *
     * @param string $fixtureName The fixture path relative to fixturesRootPath
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function loadFileFixture($fixtureName)
    {
        $fixturePath = $this->fixturesRootPath.'/'.$fixtureName;
        $format = $this->detectFormat($fixtureName);
        $ret = null;

        switch ($format) {
            case self::FORMAT_JSON:
                $ret = JSON::decode(file_get_contents($fixturePath));
                break;

            case self::FORMAT_TXT:
                $ret = file_get_contents($fixturePath);
                break;

            case self::FORMAT_SQL:
                $ret = $this->loadSql($fixturePath);
                break;
        }

        return $ret;
    }

    /**
     * Detect fixture format based on its name.
     *
     * @param string $fixturePath The fixture path
     *
     * @throws Exception
     *
     * @return string The one of self::FORMAT_* constants
     */
    public function detectFormat($fixturePath)
    {
        $info = new SplFileInfo($fixturePath);
        $extension = $info->getExtension();

        switch ($extension) {
            case self::FORMAT_JSON:
                $format = self::FORMAT_JSON;
                break;

            case self::FORMAT_TXT:
                $format = self::FORMAT_TXT;
                break;

            case self::FORMAT_SQL:
                $format = self::FORMAT_SQL;
                break;

            default:
                throw new Exception('unknown format: '.$extension);
        }

        return $format;
    }

    /**
     * Get array of SQL statements form fixture file.
     *
     * @param string $fixturePath The fixture path
     *
     * @throws \Exception
     *
     * @return array
     */
    private function loadSql($fixturePath)
    {
        if (!file_exists($fixturePath)) {
            throw new Exception("fixture $fixturePath does not exist");
        }

        $handle = @fopen($fixturePath, 'r');

        if (!$handle) {
            throw new Exception("error opening fixture $fixturePath");
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
}
