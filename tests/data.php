<?php

namespace Http {
    class Request{};
}


namespace Model {
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
            return \Util\Util::h($s);
        }

        public function otherNs1(): void
        {
            $a = new \Presenter\Form();
        }

        public function otherNs2(): void
        {
            $a = Presenter\Form::build();
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
    class ModelUtil {
        public static function getModel(): \Model\IndexModel
        {
            return new \Model\IndexModel;
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
        public static function build(): self
        {
            return new self;
        }
    }
}
