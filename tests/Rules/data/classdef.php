<?php

namespace Model {
    use OtherModel\OtherModelInterface;
    use OtherModel\OtherModelAbstract;

    class FooModel1 implements OtherModelInterface {}
    class FooModel2 extends OtherModelAbstract {}

    interface BarInterface {}
    class BarImpl implements BarInterface {}

    abstract class BazAbstract {}
    class BazImpl extends BazAbstract {}

    class BarBaz extends BazAbstract implements BarInterface {}

    class FooModel extends OtherModelAbstract implements OtherModelInterface {}
}

namespace OtherModel {
    interface OtherModelInterface {}
    abstract class OtherModelAbstract {};
}
