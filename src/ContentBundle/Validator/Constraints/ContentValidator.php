<?php
/**
 * Created by PhpStorm.
 * User: lmalicki
 * Date: 02.06.16
 * Time: 21:39
 */

namespace ContentBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContentValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($object, Constraint $constraint)
    {

        if (count($object->getChannels()) == 0) {
            $this->context->addViolation('channels', 'You must set at least one channel!');
        }

    }
}