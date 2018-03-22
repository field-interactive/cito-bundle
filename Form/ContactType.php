<?php

namespace FieldInteractive\CitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContactType
 * @package FieldInteractive\CitoBundle\Form
 */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('subject', TextType::class, [
                'empty_data' => '- no subject -',
                'required' => false,
            ])
            ->add('message', TextareaType::class)
            ->add('attachment', FileType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Send Mail'])
        ;
    }
}
