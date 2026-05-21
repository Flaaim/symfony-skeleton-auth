<?php

declare(strict_types=1);

namespace Infrastructure\Http\Test\Validator;

use Exception;
use Infrastructure\Http\Validator\ValidationException;
use Infrastructure\Http\Validator\Validator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;


final class ValidatorTest extends TestCase
{
    public function testValid(): void
    {
        $validator = new Validator($validatorInterface = $this->createMock(ValidatorInterface::class));
        $object = new stdClass();

        $validatorInterface->expects(self::once())->method('validate')
            ->with(self::equalTo($object))
        ->willReturn(new ConstraintViolationList([]));

        $validator->validate($object);
    }

    public function testNotValid(): void
    {
        $validator = new Validator($validatorInterface = $this->createMock(ValidatorInterface::class));
        $object = new stdClass();

        $validatorInterface->expects(self::once())
            ->method('validate')->willReturn($violations = new ConstraintViolationList([$this->createMock(ConstraintViolation::class)]));
        try {
            $validator->validate($object);
            self::fail('Expected exception is not thrown');
        } catch (Exception $exception) {
            self::assertInstanceOf(ValidationException::class, $exception);
            /** @var ValidationException $exception */
            self::assertEquals($violations, $exception->getViolations());
        }
    }
}
