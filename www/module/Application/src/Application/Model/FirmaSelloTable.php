<?php

namespace FirmaSello\Model;

use Zend\Db\TableGateway\TableGateway;

class FirmaSelloTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getFirma($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveFirma(FirmaSello $firma)
    {
        $data = array(
            'firma' => $firma->artist,
            'title'  => $firma->title,
        );

        $id = (int) $firma->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getfirma($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('firma id does not exist');
            }
        }
    }

    public function deleteFirma($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}