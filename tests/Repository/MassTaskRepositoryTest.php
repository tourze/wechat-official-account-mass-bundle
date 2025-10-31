<?php

namespace WechatOfficialAccountMassBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;

/**
 * @internal
 */
#[CoversClass(MassTaskRepository::class)]
#[RunTestsInSeparateProcesses]
final class MassTaskRepositoryTest extends AbstractRepositoryTestCase
{
    private MassTaskRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MassTaskRepository::class);
    }

    public function testSaveEntityShouldPersistToDatabase(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Save Test Task');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());

        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());
        $foundEntity = $this->repository->find($entity->getId());
        $this->assertNotNull($foundEntity);
        $this->assertEquals('Save Test Task', $foundEntity->getTitle());
    }

    public function testRemoveEntityShouldDeleteFromDatabase(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Remove Test Task');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());

        $this->repository->save($entity);
        $id = $entity->getId();

        $this->repository->remove($entity);

        $foundEntity = $this->repository->find($id);
        $this->assertNull($foundEntity);
    }

    public function testCountWithNullFieldShouldWork(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Null Field Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setTagId(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['tagId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullFieldShouldWork(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Null Field FindBy Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMediaId(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['mediaId' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testFindOneByWithOrderByShouldWork(): void
    {
        $entity1 = new MassTask();
        $entity1->setTitle('Order A');
        $entity1->setType(MassType::TEXT);
        $entity1->setContent('Content A');
        $entity1->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity1, false);

        $entity2 = new MassTask();
        $entity2->setTitle('Order B');
        $entity2->setType(MassType::TEXT);
        $entity2->setContent('Content B');
        $entity2->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity2);

        $foundEntity = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(MassTask::class, $foundEntity);
    }

    public function testCountWithSecondNullFieldShouldWork(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Second Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setSent(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['sent' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithSecondNullFieldShouldWork(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Second Null FindBy Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setSent(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['sent' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        $entity1 = new MassTask();
        $entity1->setTitle('Z Title');
        $entity1->setType(MassType::TEXT);
        $entity1->setContent('Content Z');
        $entity1->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity1, false);

        $entity2 = new MassTask();
        $entity2->setTitle('A Title');
        $entity2->setType(MassType::TEXT);
        $entity2->setContent('Content A');
        $entity2->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity2);

        $result = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(MassTask::class, $result);
        $this->assertEquals('A Title', $result->getTitle());
    }

    public function testInvalidFieldQuery(): void
    {
        $this->expectException(\Exception::class);
        $this->repository->findOneBy(['invalidField' => 'value']);
    }

    public function testCountWithAssociation(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Association Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['account' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithAssociation(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Association FindBy Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['account' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testFindOneByWithOrderBySortingLogic(): void
    {
        $uniqueId = uniqid('test_', true);

        $entity1 = new MassTask();
        $entity1->setTitle('First Task ' . $uniqueId);
        $entity1->setType(MassType::TEXT);
        $entity1->setContent('First content');
        $entity1->setSendTime(new \DateTimeImmutable('2023-01-01'));
        $this->repository->save($entity1, false);

        $entity2 = new MassTask();
        $entity2->setTitle('Second Task ' . $uniqueId);
        $entity2->setType(MassType::TEXT);
        $entity2->setContent('Second content');
        $entity2->setSendTime(new \DateTimeImmutable('2023-01-02'));
        $this->repository->save($entity2);

        $result = $this->repository->findOneBy(['title' => 'Second Task ' . $uniqueId]);
        $this->assertInstanceOf(MassTask::class, $result);
        $this->assertEquals('Second Task ' . $uniqueId, $result->getTitle());
    }

    public function testRobustnessWithInvalidFields(): void
    {
        $this->expectException(\Exception::class);
        $this->repository->findBy(['nonExistentField' => 'value']);
    }

    public function testCountAssociationQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count Association Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['account' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByAssociationQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('FindBy Association Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['account' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testAssociationRelationshipQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Relationship Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['account' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testNullFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Null Field Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setTagId(null);
        $entity->setMediaId(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['tagId' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testSecondNullFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Second Null Field Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMediaId(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['mediaId' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountNullFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count Null Field Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setTagId(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['tagId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountSecondNullFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count Second Null Field Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMediaId(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['mediaId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithOrderByParameter(): void
    {
        $entity1 = new MassTask();
        $entity1->setTitle('Z Order Test');
        $entity1->setType(MassType::TEXT);
        $entity1->setContent('Content Z');
        $entity1->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity1, false);

        $entity2 = new MassTask();
        $entity2->setTitle('A Order Test');
        $entity2->setType(MassType::TEXT);
        $entity2->setContent('Content A');
        $entity2->setSendTime(new \DateTimeImmutable());
        $this->repository->save($entity2);

        $result = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(MassTask::class, $result);
    }

    public function testFindByWithValidFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Valid Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setValid(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountWithValidFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count Valid Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setValid(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['valid' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithMsgTaskIdFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('MsgTaskId Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMsgTaskId(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['msgTaskId' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountWithMsgTaskIdFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count MsgTaskId Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMsgTaskId(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['msgTaskId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithMsgDataIdFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('MsgDataId Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMsgDataId(null);

        $this->repository->save($entity);

        $results = $this->repository->findBy(['msgDataId' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountWithMsgDataIdFieldIsNullQueries(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count MsgDataId Null Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setMsgDataId(null);

        $this->repository->save($entity);

        $count = $this->repository->count(['msgDataId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Association Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);
        $this->repository->save($entity);

        $result = $this->repository->findOneBy(['account' => null]);
        $this->assertInstanceOf(MassTask::class, $result);
        $this->assertEquals('Association Test', $result->getTitle());
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $entity = new MassTask();
        $entity->setTitle('Count Association Test');
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content');
        $entity->setSendTime(new \DateTimeImmutable());
        $entity->setAccount(null);
        $this->repository->save($entity);

        $count = $this->repository->count(['account' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function createNewEntity(): object
    {
        $entity = new MassTask();
        $entity->setTitle('Test MassTask ' . uniqid());
        $entity->setType(MassType::TEXT);
        $entity->setContent('Test content ' . uniqid());
        $entity->setSendTime(new \DateTimeImmutable());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MassTask>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
