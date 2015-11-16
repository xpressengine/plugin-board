<?php
/**
 * ReadCounter
 *
 * PHP version 5
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Board\Counter;

use Xpressengine\Document\DocumentEntity;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Counter\Counter;
use Xpressengine\Member\Entities\Guest;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\Board\ItemEntity;

/**
 * ReadCounter
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class ReadCounter
{
    /**
     * 조회 카운터 이름
     */
    const COUNTER_NAME = 'document_read';

    /**
     * @var DocumentHandler
     */
    protected $document;

    /**
     * @var Counter
     */
    protected $counter;

    /**
     * create instance
     *
     * @param DocumentHandler $document
     * @param Counter $counter
     */
    public function __construct(DocumentHandler $document, Counter $counter)
    {
        $this->document = $document;
        $this->counter = $counter;
    }

    /**
     * 참여 리스트
     *
     * @param $id
     * @param $option
     * @param $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($id, $option, $perPage)
    {
        $wheres = [
            'targetId' => $id,
            'counterName' => self::COUNTER_NAME,
            'counterOption' => $option,
        ];

        $orders = [
            'createdAt' => 'desc',
        ];
        return $this->counter->paginate($wheres, $orders, $perPage);
    }

    /**
     * 참여 정보 반환
     *
     * @param string                $id     document id
     * @param MemberEntityInterface $author user instance
     * @return array|null
     */
    public function get($id, MemberEntityInterface $author)
    {
        $this->counter->init(self::COUNTER_NAME);
        return $this->counter->get($id, $author);
    }

    /**
     * initialize counter
     *
     * @return void
     */
    private function init()
    {
        $this->counter->init(self::COUNTER_NAME);
    }

    /**
     * update read count
     *
     * @param DocumentEntity $doc document entity
     * @return void
     */
    private function updateCount(DocumentEntity $doc)
    {
        $count = $this->count($doc->id);
        $doc->readCount = $count;
        $this->document->rawPut($doc);
    }

    /**
     * 조회 수
     *
     * @param string $id document id
     * @return array
     */
    public function count($id)
    {
        $this->init();
        return $this->counter->count($id);
    }

    /**
     * 참여 여부 반환
     *
     * @param string                $id     document id
     * @param MemberEntityInterface $author user instance
     * @return bool
     */
    public function invoked($id, MemberEntityInterface $author)
    {
        $this->init();
        return $this->counter->invoked($id, $author);
    }

    /**
     * 찬성
     *
     * @param ItemEntity            $item   board item entity
     * @param MemberEntityInterface $author user instance
     * @return void
     */
    public function add(ItemEntity $item, MemberEntityInterface $author)
    {
        if ($this->invoked($item->id, $author) === false) {
            $doc = $item->getDocument();
            $this->init();
            $this->counter->add($doc->id, $author);
            $this->updateCount($doc);
        }
    }

    /**
     * 반대
     *
     * @param ItemEntity            $item   board item entity
     * @param MemberEntityInterface $author user instance
     * @return void
     */
    public function remove(ItemEntity $item, MemberEntityInterface $author)
    {
        $doc = $item->getDocument();
        $this->init();
        $this->counter->remove($doc->id, $author, $option);
        $this->updateCount($doc);
    }
}
