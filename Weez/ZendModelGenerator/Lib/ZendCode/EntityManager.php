<?php

namespace Weez\ZendModelGenerator\Lib\ZendCode;

use \Zend\Code\Generator\ClassGenerator;
use \Zend\Code\Generator\DocBlock\Tag\ParamTag;
use \Zend\Code\Generator\DocBlock\Tag\PropertyTag;
use \Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use \Zend\Code\Generator\DocBlockGenerator;
use \Zend\Code\Generator\MethodGenerator;
use \Zend\Code\Generator\ParameterGenerator;
use \Zend\Code\Generator\PropertyGenerator;

/**
 * Description of Entity
 *
 * @author teddy
 */
class EntityManager extends AbstractGenerator
{

    private $data;

    public function getClassArrayRepresentation()
    {
        $this->data = $this->getData();

        $methods = $this->getConstructor();
        $methods = array_merge($methods, $this->getMethods());
        $methods = array_merge($methods, $this->getSaveEntityMethod());
        $methods = array_merge($methods, $this->getDeleteEntityMethod());
        $methods = array_merge($methods, $this->getUtils());


        return array(
            'name'          => $this->data['_className'],
            'namespacename' => $this->data['_namespace'] . '\Table',
            'extendedclass' => 'Manager',
            'docblock'      => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Application Entity Manager',
                        'longDescription'  => null,
                        'tags'             => array(
                            array(
                                'name'        => 'package',
                                'description' => $this->data['_namespace'],
                            ),
                            array(
                                'name'        => 'author',
                                'description' => $this->data['_author'],
                            ),
                            array(
                                'name'        => 'copyright',
                                'description' => $this->data['_copyright'],
                            ),
                            array(
                                'name'        => 'license',
                                'description' => $this->data['_license'],
                            ),
                        )
                    )
            ),
            'properties'    => $this->getProperties(),
            'methods'       => $methods
        );
    }

    private function getProperties()
    {
        $classProperties   = array();
        $classProperties[] = PropertyGenerator::fromArray(array(
                    'name'         => 'table',
                    'defaultvalue' => $this->data['_tbname'],
                    'flags'        => PropertyGenerator::FLAG_PROTECTED,
                    'docblock'     => DocBlockGenerator::fromArray(array(
                        'shortDescription' => 'Name of database table ',
                        'longDescription'  => null,
                        'tags'             => array(
                            new PropertyTag('table', array('string')),
                        )
                    ))
        ));
        $classProperties[] = PropertyGenerator::fromArray(array(
                    'name'         => 'tableName',
                    'defaultvalue' => $this->data['_tbname'],
                    'flags'        => PropertyGenerator::FLAG_PROTECTED,
                    'docblock'     => DocBlockGenerator::fromArray(array(
                        'shortDescription' => 'Name of table ',
                        'longDescription'  => null,
                        'tags'             => array(
                            new PropertyTag('tableName', array('string')),
                        )
                    ))
        ));
        $classProperties[] = PropertyGenerator::fromArray(array(
                    'name'         => 'id',
                    'defaultvalue' => 'array' !== $this->data['_primaryKey']['phptype'] ? $this->data['_primaryKey']['field'] : eval('return ' . $this->data['_primaryKey']['field'] . ';'),
                    'flags'        => PropertyGenerator::FLAG_PROTECTED,
                    'docblock'     => DocBlockGenerator::fromArray(array(
                        'shortDescription' => 'Primary key name',
                        'longDescription'  => null,
                        'tags'             => array(
                            new PropertyTag('id', array($this->data['_primaryKey']['phptype'])),
                        )
                    ))
        ));
        $classProperties[] = PropertyGenerator::fromArray(array(
                    'name'         => 'sequence',
                    'defaultvalue' => 'array' !== $this->data['_primaryKey']['phptype'],
                    'flags'        => PropertyGenerator::FLAG_PROTECTED,
                    'docblock'     => DocBlockGenerator::fromArray(array(
                        'shortDescription' => '',
                        'longDescription'  => null,
                        'tags'             => array(
                            new PropertyTag('sequence', array('boolean')),
                        )
                    ))
        ));
        return $classProperties;
    }

    private function getConstructor()
    {
        $constructBody = 'parent::__construct($adapter,new ' . $this->data['_className'] . 'Entity());' . PHP_EOL;
        $methods       = array(
            array(
                'name'       => '__construct',
                'parameters' => array(
                    ParameterGenerator::fromArray(array(
                        'name' => 'adapter',
                            //'type' => 'Adapter',
                    ))
                ),
                'flags'      => MethodGenerator::FLAG_PUBLIC,
                'body'       => $constructBody,
                'docblock'   => DocBlockGenerator::fromArray(
                        array(
                            'shortDescription' => 'Sets up column and relationship lists',
                            'longDescription'  => null,
                            'tags'             => array(
                                new ParamTag('adapter', array('Adapter')),
                            )
                        )
                )
            ),
        );
        return $methods;
    }

    private function getMethods()
    {
        $constructBody = 'return $this->all();';
        $methods[]     = array(
            'name'       => 'fetchAll',
            'parameters' => array(
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Fetch all Entity',
                        'longDescription'  => null,
                        'tags'             => array(
                            new ReturnTag(array('datatype' => 'array of ' . $this->data['_className'])),
                        )
                    )
            )
        );
        $constructBody = '' . PHP_EOL;
        $constructBody .= '$rowset = $this->select(' . PHP_EOL;
        if ($this->data['_primaryKey']['phptype'] !== 'array') {
            $constructBody .='      array(\'' . $this->data['_primaryKey']['field'] . '\' => $id)' . PHP_EOL;
        } else {
            $constructBody .= '$id' . PHP_EOL;
        }
        $constructBody .= ');' . PHP_EOL;
        $constructBody .= '$row = $rowset->current();' . PHP_EOL;
        $constructBody .= 'if (!$row) {' . PHP_EOL;
        $constructBody .= '     return null;' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        $constructBody .= 'return $row->getArrayCopy();' . PHP_EOL;
        $methods[] = array(
            'name'       => 'find',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name' => 'id',
                    'type' => $this->data['_primaryKey']['phptype'],
                ))
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Finds row by primary key',
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('id', array($this->data['_primaryKey']['phptype'])),
                            new ReturnTag(array('datatype' => $this->data['_namespace'] . '\\' . $this->data['_className'] . '|null')),
                        )
                    )
            )
        );


        $constructBody = '' . PHP_EOL;
        $constructBody .= 'return $this->select($criteria)->toArray();' . PHP_EOL;
        $methods[]     = array(
            'name'       => 'findBy',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name'         => 'criteria',
                    'defaultvalue' => array(),
                ))
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Find by criteria',
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('criteria', array($this->data['_primaryKey']['phptype'])),
                            new ReturnTag(array('datatype' => 'array of \'' . $this->data['_namespace'] . '\\' . $this->data['_className'] . '|null')),
                        )
                    )
            )
        );

        return $methods;
    }

    private function getDeleteEntityMethod()
    {
        $constructBody = '' . PHP_EOL;
        $constructBody .= 'if (! $entity instanceof ' . $this->data['_className'] . 'Entity ){' . PHP_EOL;
        $constructBody .= '     throw new \Exception(\'Unable to delete: invalid entity\');' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        $constructBody .= 'if ($useTransaction) {' . PHP_EOL;
        $constructBody .= '     $this->adapter->getDriver()->getConnection()->beginTransaction();' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        $constructBody .= 'try {' . PHP_EOL;
        if ($this->data['_softDeleteColumn'] != null) {
            foreach ($this->data['_columns'] as $column) {
                if ($column['field'] == $this->data['_softDeleteColumn']) {
                    $constructBody .= '     $entity->set' . $column['capital'] . '(' . $column['phptype'] == 'boolean' ? 'true' : '1' . ');' . PHP_EOL;
                    break;
                }
            }
            $constructBody .= '     $result = $this->save($entity);' . PHP_EOL;
        } else {
            if ($this->data['_primaryKey']['phptype'] == 'array') {
                $constructBody .= '     $where = array();' . PHP_EOL;
                foreach ($this->data['_primaryKey']['fields'] as $key) {
                    $constructBody .= '     $pk_val = $entity->get' . $key['capital'] . '();' . PHP_EOL;
                    $constructBody .= '         if ($pk_val === null) {' . PHP_EOL;
                    $constructBody .= '             throw new \Exception(\'The value for ' . $key['capital'] . 'cannot be null\');' . PHP_EOL;
                    $constructBody .= '         } else {' . PHP_EOL;
                    $constructBody .= '             $where[] =  array(\'' . $key['field'] . '\' => $pk_val); ' . PHP_EOL;
                    $constructBody .= '         }' . PHP_EOL;
                }
            } else {
                $constructBody .= '     $where = array(\'' . $this->data['_primaryKey']['field'] . '\' => $entity->get' . $this->data['_primaryKey']['capital'] . '());' . PHP_EOL;
            }
            $constructBody .= '     $result = $this->delete($where);' . PHP_EOL;
        }

        $constructBody .= '     if ($useTransaction) {' . PHP_EOL;
        $constructBody .= '         $this->adapter->getDriver()->getConnection()->commit();' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        $constructBody .= '} catch (Exception $e) {' . PHP_EOL;
        $constructBody .= '     if ($useTransaction) {' . PHP_EOL;
        $constructBody .= '         $this->adapter->getDriver()->getConnection()->rollback();' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        $constructBody .= '     $result = false;' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        $constructBody .= 'return $result;' . PHP_EOL;
        $constructBody .= '' . PHP_EOL;
        $methods[] = array(
            'name'       => 'deleteEntity',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name' => 'entity',
                    'type' => 'Entity',
                )),
                ParameterGenerator::fromArray(array(
                    'name'         => 'useTransaction',
                    'defaultValue' => true,
                )),
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Deletes the current entity',
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('entity', array('Entity')),
                            new ParamTag('useTransaction', array('boolean'), 'Flag to indicate if delete should be done inside a database transaction'),
                            new ReturnTag(array('datatype' => 'Inserted id|false')),
                        )
                    )
            )
        );
        return $methods;
    }

    private function getSaveEntityMethod()
    {
        $constructBody = '' . PHP_EOL;
        $constructBody .= '$data = $entity->toArray();' . PHP_EOL;
        $constructBody .= 'if ($ignoreEmptyValues) {' . PHP_EOL;
        $constructBody .= '     foreach ($data as $key => $value) {' . PHP_EOL;
        $constructBody .= '         if ($value === null or $value === \'\') {' . PHP_EOL;
        $constructBody .= '             unset($data[$key]);' . PHP_EOL;
        $constructBody .= '         }' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        if ($this->data['_primaryKey']['phptype'] == 'array') {
            $constructBody .= '$primary_key = array();' . PHP_EOL;
            foreach ($this->data['_primaryKey']['fields'] as $key) {
                $constructBody .= '$pk_val = $entity->get' . $key['capital'] . '();' . PHP_EOL;
                $constructBody .= 'if ($pk_val === null) {' . PHP_EOL;
                $constructBody .= '     return false;' . PHP_EOL;
                $constructBody .= '} else {' . PHP_EOL;
                $constructBody .= '     $primary_key[\'' . $key['field'] . '\'] =  $pk_val;' . PHP_EOL;
                $constructBody .= '}' . PHP_EOL;
            }
            $constructBody .= '$exists = $this->find($primary_key);' . PHP_EOL;
            $constructBody .= '$success = true;' . PHP_EOL;
            $constructBody .= 'if ($useTransaction) {' . PHP_EOL;
            $constructBody .= '     $this->adapter->getDriver()->getConnection()->beginTransaction();' . PHP_EOL;
            $constructBody .= '}' . PHP_EOL;
            $constructBody .= 'try {' . PHP_EOL;
            $constructBody .= '     // Check for current existence to know if needs to be inserted' . PHP_EOL;
            $constructBody .= '     if ($exists === null) {' . PHP_EOL;
            $constructBody .= '         $this->insert($data);' . PHP_EOL;
        } else {
            $constructBody .= '$primary_key = $entity->get' . $this->data['_primaryKey']['capital'] . '();' . PHP_EOL;
            $constructBody .= '$success = true;' . PHP_EOL;
            $constructBody .= 'if ($useTransaction) {' . PHP_EOL;
            $constructBody .= '     $this->adapter->getDriver()->getConnection()->beginTransaction();' . PHP_EOL;
            $constructBody .= '}' . PHP_EOL;
            if (!$this->data['_primaryKey']['foreign_key']) {
                $constructBody .= 'unset($data[\'' . $this->data['_primaryKey']['field'] . '\']);' . PHP_EOL;
                $constructBody .= 'try {' . PHP_EOL;
                $constructBody .= '     if ($primary_key === null) {' . PHP_EOL;
            } else {
                $constructBody .= '$exists = $this->find($primary_key);' . PHP_EOL;
                $constructBody .= 'try {' . PHP_EOL;
                $constructBody .= '     if ($exists === null) {' . PHP_EOL;
            }
            $constructBody .= '         $this->insert($data);' . PHP_EOL;
            $constructBody .= '         $primary_key = $this->getLastInsertValue();' . PHP_EOL;
            $constructBody .= '         if ($primary_key) {' . PHP_EOL;
            $constructBody .= '             $entity->set' . $this->data['_primaryKey']['capital'] . '($primary_key);' . PHP_EOL;
            if ($this->data['_returnId']) {
                $constructBody .= '         $success = $primary_key;' . PHP_EOL;
            }
            $constructBody .= '         } else {' . PHP_EOL;
            $constructBody .= '             $success = false;' . PHP_EOL;
            $constructBody .= '         }' . PHP_EOL;
        }
        $constructBody .= '     } else {' . PHP_EOL;
        $constructBody .= '         $this->update($data,array(' . PHP_EOL;
        if ($this->data['_primaryKey']['phptype'] == 'array') {
            $fields = count($this->data['_primaryKey']['fields']);
            foreach ($this->data['_primaryKey']['fields'] as $key) {
                $constructBody .= '             \'' . $key['field'] . ' = ?\' => $primary_key[\'' . $key['field'] . '\'],' . PHP_EOL;
            }
        } else {
            $constructBody .='              \'' . $this->data['_primaryKey']['field'] . ' = ?\' => $primary_key' . PHP_EOL;
        }
        $constructBody .= '             )' . PHP_EOL;
        $constructBody .= '         );' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        if (count($this->data['dependentTables']) > 0) {
            $constructBody .= '     if ($recursive) {' . PHP_EOL;
            foreach ($this->data['dependentTables'] as $key) {
                $constructBody .= '         if ($success && $entity->get' . $this->data['relationNameDependent'][$key['key_name']] . '() !== null) {' . PHP_EOL;
                if ($key['type'] !== 'many') {
                    $constructBody .= '$success = $success &&  $entity->get' . $this->data['relationNameDependent'][$key['key_name']] . '()' . PHP_EOL;
                    if ($this->data['_primaryKey']['phptype'] !== 'array') {
                        $constructBody .= '->set' . $this->_getCapital($key['column_name']) . '($primary_key)' . PHP_EOL;
                    }
                    $constructBody .= '->save($ignoreEmptyValues, $recursive, false);' . PHP_EOL;
                } else {
                    $constructBody .= '             $' . $this->data['classNameDependent'][$key['key_name']]['foreign_tbl_name'] . ' = $entity->get' . $this->data['relationNameDependent'][$key['key_name']] . '();' . PHP_EOL;
                    $constructBody .= '             foreach ($' . $this->data['classNameDependent'][$key['key_name']]['foreign_tbl_name'] . ' as $value) {' . PHP_EOL;
                    $constructBody .= '                 $success = $success && $value' . PHP_EOL;
                    if ($this->data['_primaryKey']['phptype'] !== 'array') {
                        $constructBody .= '                 ->set' . $this->_getCapital($key['column_name']) . '($primary_key)' . PHP_EOL;
                    } elseif (is_array($key['column_name'])) {
                        foreach ($key['column_name'] as $column) {
                            $constructBody .= '         ->set' . $this->_getCapital($column) . '($primary_key[\'' . $column . '\'])' . PHP_EOL;
                        }
                    }
                    $constructBody .= '                 ->save($ignoreEmptyValues, $recursive, false);' . PHP_EOL;
                    $constructBody .= '                 if (! $success) {' . PHP_EOL;
                    $constructBody .= '                     break;' . PHP_EOL;
                    $constructBody .= '                 }' . PHP_EOL;
                    $constructBody .= '             }' . PHP_EOL;
                }
                $constructBody .= '         }' . PHP_EOL;
            }
            $constructBody .= '     }' . PHP_EOL;
        }
        $constructBody .= '     if ($useTransaction && $success) {' . PHP_EOL;
        $constructBody .= '         $this->adapter->getDriver()->getConnection()->commit();' . PHP_EOL;
        $constructBody .= '     } elseif ($useTransaction) {' . PHP_EOL;
        $constructBody .= '         $this->adapter->getDriver()->getConnection()->rollback();' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        $constructBody .= '} catch (Exception $e) {' . PHP_EOL;
        $constructBody .= '     if ($useTransaction) {' . PHP_EOL;
        $constructBody .= '         $this->adapter->getDriver()->getConnection()->rollback();' . PHP_EOL;
        $constructBody .= '     }' . PHP_EOL;
        $constructBody .= '     $success = false;' . PHP_EOL;
        $constructBody .= '}' . PHP_EOL;
        $constructBody .= 'return $success;' . PHP_EOL;
        $methods[] = array(
            'name'       => 'saveEntity',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name' => 'entity',
                    'type' => 'Entity',
                )),
                ParameterGenerator::fromArray(array(
                    'name'         => 'ignoreEmptyValues',
                    'defaultValue' => true,
                )),
                ParameterGenerator::fromArray(array(
                    'name'         => 'recursive',
                    'defaultValue' => false,
                )),
                ParameterGenerator::fromArray(array(
                    'name'         => 'useTransaction',
                    'defaultValue' => true,
                )),
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => 'Saves current row, and optionally dependent rows',
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('entity', array('Entity')),
                            new ParamTag('ignoreEmptyValues', array('boolean'), 'Should empty values saved'),
                            new ParamTag('recursive', array('boolean'), 'Should the object graph be walked for all related elements'),
                            new ParamTag('useTransaction', array('boolean'), 'Flag to indicate if save should be done inside a database transaction'),
                            new ReturnTag(array('datatype' => 'Inserted id|false')),
                        )
                    )
            )
        );
        return $methods;
    }

    private function getUtils()
    {
        $constructBody = 'return $this->adapter->platform->quoteIdentifier($name);
';
        $methods[]     = array(
            'name'       => 'qi',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name' => 'name',
                    'type' => 'string',
                ))
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => null,
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('name', array('string')),
                            new ReturnTag(array('datatype' => 'string')),
                        )
                    )
            )
        );
        $constructBody = 'return $this->adapter->driver->formatParameterName($name);
';
        $methods[]     = array(
            'name'       => 'fp',
            'parameters' => array(
                ParameterGenerator::fromArray(array(
                    'name' => 'name',
                    'type' => 'string',
                ))
            ),
            'flags'      => MethodGenerator::FLAG_PUBLIC,
            'body'       => $constructBody,
            'docblock'   => DocBlockGenerator::fromArray(
                    array(
                        'shortDescription' => null,
                        'longDescription'  => null,
                        'tags'             => array(
                            new ParamTag('name', array('string')),
                            new ReturnTag(array('datatype' => 'string')),
                        )
                    )
            )
        );
        return $methods;
    }

    /**
     *
     * @return type
     */
    public function generate()
    {
        $class         = ClassGenerator::fromArray($this->getClassArrayRepresentation());
        $class->addUse($this->data['_namespace'] . '\Table\Manager')
                ->addUse('Zend\Db\Adapter\Adapter')
                ->addUse($this->data['_namespace'] . '\Entity\\Entity')
                ->addUse($this->data['_namespace'] . '\Entity\\' . $this->data['_className'], $this->data['_className'] . 'Entity')
        ;
        $fileGenerator = $this->getFileGenerator();
        return $fileGenerator
                        ->setClass($class)
                        ->generate();
    }

}
