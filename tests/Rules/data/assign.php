<?php

namespace Model {
    use Util\DateUtil;

    class DateModel {
        public function show(): void
        {
            $date = DateUtil::toDateTime('2020-01-01 12:34:56');
            echo $date->format('Y-m-d H:i:s');
        }
    }
}

namespace Util {
    use DateTimeInterface;
    use DateTimeImmutable;

    class DateUtil {
        public static function toDateTime(string $datetime): DateTimeInterface
        {
            return new DateTimeImmutable($datetime);
        }
    }
}
