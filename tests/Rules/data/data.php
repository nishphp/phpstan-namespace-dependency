<?php

namespace Http {
    class Request{};
}


namespace Model {
    use Util\Util;
    use Presenter\Form;

    class IndexModel {
        /** @var \stdClass */
        private $obj;

        public function setObj(\stdClass $obj)
        {
        }

        public function getObj(): \stdClass
        {
            return new \stdClass;
        }

        public function h(string $s): string
        {
            return Util::h($s);
        }

        public function otherNs1(): void
        {
            $a = new Form();
        }

        public function otherNs2(): void
        {
            $a = Form::build();
        }

        public function otherNs3(): void
        {
            $a = Form::$a;
        }

        public function otherNs4(): void
        {
            $a = Form::B;
        }
    }
}

namespace Controller {
    class IndexController {

        public static function index(Request $request): \View\IndexView
        {
            return new \View\IndexView();
        }
        private static function calc(): string
        {
            $model = new \Model\IndexModel();
            return $model->h('foo');
        }
    }
}

namespace View {
    class IndexView {
    }
}

namespace Util {
    use Model\IndexModel;

    class ModelUtil {
        public static function getModel(): IndexModel
        {
            return new IndexModel;
        }
    }

    class Util {
        public static function h(string $s): string
        {
            return htmlspecialchars($s);
        }
    }

    class Container {
        public static function getView(): \View\IndexView
        {
            return new \View\IndexView;
        }
    }
}

namespace Presenter {
    class Form {
        public static $a;
        const B = 'b';
        public static function build(): self
        {
            return new self;
        }
    }
}

namespace Model
{
    class SelfModel {
        const DATA1 = 1;

        public function getData1(): int
        {
            return self::DATA1;
        }
    }
}
