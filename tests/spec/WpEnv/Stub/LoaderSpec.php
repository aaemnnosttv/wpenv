<?php namespace spec\WpEnv\Stub;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoaderSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith( __DIR__ . '/../../../fixtures/dummy-loader-data.yml' );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('WpEnv\Stub\Loader');
    }

    function it_sqwaks_if_it_is_instantiated_without_a_filepath()
    {
        $this->shouldThrow('\\InvalidArgumentException')->during('__construct', []);
    }

    function it_squaks_if_it_is_instantiated_with_a_broken_yml_file()
    {
        $this->shouldThrow('\\Symfony\\Component\\Yaml\\Exception\\ParseException')
            ->during('__construct', [__DIR__ . '/../../../fixtures/broken.yml']);
    }

    function it_returns_true_if_data_is_set_for_all_required_keys_it_checks()
    {
        // top level
        $this->required(['foo']);
        // hierarchical
        $this->required(['bar.baz','bar.booze']);
    }

    function it_throws_an_exception_if_there_are_missing_required_keys()
    {
        // top-level
        $this->shouldThrow('\\Exception', "Missing required keys: " . json_encode(['missing']))
            ->during('required', [ ['missing'] ]);

        // multiple top-level
        $this->shouldThrow('\\Exception', "Missing required keys: " . json_encode(['missing','doesnotexist']))
            ->during('required', [ ['missing', 'doesnotexist'] ]);

        // existing parent, missing child
        $this->shouldThrow('\\Exception', "Missing required keys: " . json_encode(['exists.doesnotexist']))
            ->during('required', [ ['exists.doesnotexist'] ]);
    }
}
