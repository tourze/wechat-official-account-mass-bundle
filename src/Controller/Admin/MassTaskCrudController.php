<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

/**
 * 群发任务管理控制器
 */
#[AdminCrud(routePath: '/wechat-official-account-mass/mass-task', routeName: 'wechat_official_account_mass_mass_task')]
final class MassTaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MassTask::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('群发任务')
            ->setEntityLabelInPlural('群发任务')
            ->setPageTitle(Crud::PAGE_INDEX, '群发任务列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建群发任务')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑群发任务')
            ->setPageTitle(Crud::PAGE_DETAIL, '群发任务详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setDateTimeFormat('yyyy-MM-dd HH:mm:ss')
            ->setTimezone('Asia/Shanghai')
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '公众号账户'))
            ->add(ChoiceFilter::new('type', '消息类型')->setChoices([
                '文本' => MassType::TEXT,
                '语音' => MassType::VOICE,
            ]))
            ->add(BooleanFilter::new('sent', '发送状态'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(DateTimeFilter::new('sendTime', '发送时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            TextField::new('title', '任务名称')
                ->setRequired(true)
                ->setMaxLength(100)
                ->setHelp('群发任务的名称，用于识别任务')
                ->setColumns(6),

            AssociationField::new('account', '公众号账户')
                ->setRequired(false)
                ->setHelp('选择要发送群发消息的公众号账户')
                ->setColumns(6),

            ChoiceField::new('type', '消息类型')
                ->setRequired(true)
                ->setChoices([
                    '文本' => MassType::TEXT,
                    '语音' => MassType::VOICE,
                ])
                ->renderExpanded()
                ->setHelp('选择群发消息的类型')
                ->setColumns(6),

            TextareaField::new('content', '消息内容')
                ->setRequired(true)
                ->setMaxLength(65535)
                ->setHelp('群发消息的具体内容')
                ->setNumOfRows(8)
                ->hideOnIndex(),

            DateTimeField::new('sendTime', '发送时间')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('计划发送群发消息的时间')
                ->setColumns(6),

            TextField::new('tagId', '标签ID')
                ->setRequired(false)
                ->setHelp('要发送的用户标签ID，为空则发送给所有用户')
                ->hideOnIndex()
                ->setColumns(6),

            ArrayField::new('openIds', 'OpenID列表')
                ->setRequired(false)
                ->setHelp('指定发送的用户OpenID列表，优先级高于标签')
                ->hideOnIndex(),

            TextField::new('mediaId', '媒体ID')
                ->setRequired(false)
                ->setMaxLength(120)
                ->setHelp('语音类型消息的媒体ID')
                ->hideOnIndex()
                ->setColumns(6),

            BooleanField::new('sent', '已发送')
                ->setRequired(false)
                ->setHelp('任务是否已经发送')
                ->renderAsSwitch(false),

            BooleanField::new('valid', '有效状态')
                ->setRequired(false)
                ->setHelp('任务是否有效')
                ->renderAsSwitch(false),

            TextField::new('msgTaskId', '微信任务ID')
                ->setRequired(false)
                ->setMaxLength(60)
                ->setHelp('微信返回的群发任务ID')
                ->hideOnForm()
                ->hideOnIndex(),

            TextField::new('msgDataId', '消息数据ID')
                ->setRequired(false)
                ->setMaxLength(60)
                ->setHelp('微信返回的消息数据ID')
                ->hideOnForm()
                ->hideOnIndex(),

            DateTimeField::new('createTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updateTime', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnIndex(),
        ];
    }
}
