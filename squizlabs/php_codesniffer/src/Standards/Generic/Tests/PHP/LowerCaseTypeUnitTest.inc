<?php
$foo = (int) $bar;
$foo = (integer) $bar;
$foo = (bool) $bar;
$foo = (boolean) $bar;
$foo = (float) $bar;
$foo = (double) $bar;
$foo = (real) $bar;
$foo = (string) $bar;
$foo = (array) $bar;
$foo = (object) $bar;
$foo = (unset) $bar;

$foo = (Int) $bar;
$foo = (INTEGER) $bar;
$foo = (BOOL) $bar;
$foo = (String) $bar;
$foo = ( Array ) $bar;

function foo(int $a, string $b, bool $c, array $d, Foo\Bar $e) : int {}
function foo(Int $a, String $b, BOOL $c, Array $d, Foo\Bar $e) : Foo\Bar {}
function foo(Int $a, Bar $b, BOOL $c, Array $d, Foo\Bar $e) : Bar {}
function foo(callable $a, Callable $b, self $c, Iterable $d, iterable $e) : Float {}

$foo = function (int $a, Bool $b) {};
$foo = function (int $a, Callable $b) :INT{};
$foo = function (BOOL $a, float $b) use ($foo) : INT {};
$foo = function (Foo $a, Foo\Bar $b) use ($foo) : \Foo\Bar {};
$foo = function (bool $a, callable $b) use ($foo) : Bar {};

class Testing {
    public function TestThis(SELF $a, obJect $b, Parent $c) : VOID {}
}

function foo(
    ?Float $a,
    ? String $b,
    ?ITERABLE $c,
    ?	Object $d,
    ?Foo\Bar $e
) : ?Foo\Bar {}

$foo = function (?Int $a, ?    Callable $b)
    :?INT{};

$var = (BInARY) $string;
$var = (binary)$string;

function unionParamTypesA (bool|array| /* nullability operator not allowed in union */ NULL $var) {}

function unionParamTypesB (\Package\ClassName | Int | \Package\Other_Class | FALSE $var) {}

function unionReturnTypesA ($var): bool|array| /* nullability operator not allowed in union */ NULL {}

function unionReturnTypesB ($var): \Package\ClassName | Int | \Package\Other_Class | FALSE {}

class TypedProperties
{
    protected ClassName $class;
    public Int $int;
    private ?BOOL $bool;
    public Self $self;
    protected PaRenT $parent;
    private ARRAY $array;
    public Float $float;
    protected ?STRING $string;
    private IterablE $iterable;
    public Object $object;
    protected Mixed $mixed;

    public Iterable|FALSE|NULL $unionTypeA;
    protected SELF|Parent /* comment */ |\Fully\Qualified\ClassName|UnQualifiedClass $unionTypeB;
    private ClassName|/*comment*/Float|STRING|False $unionTypeC;
    public sTRing | aRRaY | FaLSe $unionTypeD;
}

class ConstructorPropertyPromotionWithTypes {
    public function __construct(protected Float|Int $x, public(set) ?STRING &$y = 'test', private mixed $z) {}
}

class ConstructorPropertyPromotionAndNormalParams {
    public function __construct(public Int $promotedProp, ?Int $normalArg) {}
}

function (): NeVeR {
    exit;
};

function intersectionParamTypes (\Package\ClassName&\Package\Other_Class $var) {}

function intersectionReturnTypes ($var): \Package\ClassName&\Package\Other_Class {}

$arrow = fn (int $a, string $b, bool $c, array $d, Foo\Bar $e) : int => $a * $b;
$arrow = fn (Int $a, String $b, BOOL $c, Array $d, Foo\Bar $e) : Float => $a * $b;

$cl = function (False $a, TRUE $b, Null $c): ?True {};

class TypedClassConstants
{
    const UNTYPED = null;
    const FLOAT = 'Reserved keyword as name is valid and should not be changed';
    const OBJECT = 'Reserved keyword as name is valid and should not be changed';

    const ClassName FIRST = null;
    public const Int SECOND = 0;
    private const ?BOOL THIRD = false;
    public const Self FOURTH = null;
}
interface TypedInterfaceConstants
{
    protected const PaRenT FIRST = null;
    private const ARRAY SECOND = [];
    public const Float THIRD = 2.5;
    final const ?STRING FOURTH = 'fourth';
}
trait TypedTraitConstants {
    const IterablE FIRST = null;
    const Object SECOND = null;
    const Mixed THIRD = 'third';
}
enum TypedEnumConstants {
    public const Iterable|FALSE|NULL FIRST = null;
    protected const SELF|Parent /* comment */ |\Fully\Qualified\ClassName|UnQualifiedClass SECOND = null;
    private const ClassName|/*comment*/Float|STRING|False THIRD = 'third';
    public const sTRing | aRRaY | FaLSe FOURTH = 'fourth';
}

class DNFTypes {
    const (Parent&Something)|Float CONST_NAME = 1.5;

    public readonly TRUE|(\A&B) $prop;

    function DNFParamTypes (
        null|(\Package\ClassName&\Package\Other_Class)|INT $DNFinMiddle,
        (\Package\ClassName&\Package\Other_Class)|ARRAY $parensAtStart,
        False|(\Package\ClassName&\Package\Other_Class) $parentAtEnd,
    ) {}

    function DNFReturnTypes ($var): object|(Self&\Package\Other_Class)|sTRINg|false {}
}

// Intentional error, should be ignored by the sniff.
interface PropertiesNotAllowed {
    public $notAllowed;
}
