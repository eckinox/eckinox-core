<?php

namespace Eckinox;

class Git {

    public static function getCommit() {
        return trim(`git log --pretty="%h" -n1 HEAD`);
    }

    public static function getBranch() {
        return trim(`git branch | grep \*`, "*\ \n");
    }

    public static function getCommitDate($format = "%F") {
        $commit = static::getCommit();
        $time = trim(`git show -s --format=%ct $commit`);

        return strftime($format, $time);
    }

    public static function getUpdateCount($origin = null, $branch = null) {
        $origin ?? $origin = 'origin';
        $branch ?? $branch = static::getBranch();

        return trim(`git rev-list HEAD...$origin/$branch --count`);
    }

    public static function getFileChanged() {
        return trim(`git status --porcelain`);
    }

    public static function pull($origin = null, $branch = null) {
        $origin ?? $origin = 'origin';
        $branch ?? $branch = static::getBranch();

        return trim(`git pull $origin $branch`);
    }
}
