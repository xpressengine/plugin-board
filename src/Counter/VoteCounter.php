<?php
/**
 * VoteCounter
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

use Xpressengine\Document\DocumentHandler;
use Xpressengine\Counter\Counter;
use Xpressengine\Member\Entities\Guest;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\Board\ItemEntity;

/**
 * VoteCounter
 *
 * @category    Board
 * @package     Xpressengine\Plugins\Board
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class VoteCounter
{
    /**
     * 투표 카운터 이름
     */
    const COUNTER_NAME = 'document_vote';

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
     * @param string        $id     document id
     * @param MemberEntityInterface $author user instance
     * @return array|null
     */
    public function get($id, MemberEntityInterface $author)
    {
        $this->counter->init(self::COUNTER_NAME);
        return $this->counter->get($id, $author);
    }

    /**
     * 문서의 투표 수 정보 반환
     * 찬서(assent), 반대(dissent) 수 반환
     *
     * @param string $id document id
     * @return array
     */
    public function count($id)
    {
        $this->counter->init(self::COUNTER_NAME);

        $counts = ['assent' => 0, 'dissent' => 0];
        foreach ($this->counter->countsByOption($id) as $row) {
            $counts[$row['counterOption']] = $row['count'];
        }

        return $counts;
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
        // Guest 는 참여안한 것으로 처리하고 투표할 때 접근할 수 없도록 처리
        if ($author instanceof Guest) {
            return false;
        }

        $this->counter->init(self::COUNTER_NAME);
        return $this->counter->invoked($id, $author);
    }

    /**
     * 찬성
     *
     * @param ItemEntity            $item   board item entity
     * @param MemberEntityInterface $author user instance
     * @param string                $option 'assent' or 'dissent'
     * @return void
     */
    public function add(ItemEntity $item, MemberEntityInterface $author, $option)
    {
        $doc = $item->getDocument();
        $this->counter->init(self::COUNTER_NAME, $option);
        $this->counter->add($doc->id, $author);

        $count = $this->count($doc->id);
        if ($option == 'assent') {
            $doc->assentCount = $count['assent'];
        } elseif ($option == 'dissent') {
            $doc->dissentCount = $count['dissent'];
        }
        $this->document->rawPut($doc);
    }

    /**
     * 반대
     *
     * @param ItemEntity            $item   board item entity
     * @param MemberEntityInterface $author user instance
     * @param string                $option 'assent' or 'dissent'
     * @return void
     */
    public function remove(ItemEntity $item, MemberEntityInterface $author, $option)
    {
        $doc = $item->getDocument();
        $this->counter->init(self::COUNTER_NAME, $option);
        $this->counter->remove($doc->id, $author, $option);

        $count = $this->count($doc->id);
        if ($option == 'assent') {
            $doc->assentCount = $count['assent'];
        } elseif ($option == 'dissent') {
            $doc->dissentCount = $count['dissent'];
        }
        $this->document->rawPut($doc);
    }
}
