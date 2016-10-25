<?php
/**
 * Created by PhpStorm.
 * User: lmalicki
 * Date: 02.06.16
 * Time: 21:43
 */

namespace ContentBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Content extends Constraint
{
    public function validatedBy()
    {
        return 'content.form_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
