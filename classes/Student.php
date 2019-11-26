<?php
/**
 *
 * Используя код престашоп. Написать класс(объектную модель) и SQL запрос к нему для объектной модели студентов в группе.
 * У каждого студента есть:
 * - Имя(может различаться в разных языках)
 * - Дата рождения
 * - Учиться\отчислен
 * - Средний бал
 * В модели нужны следующие методы:
 * - Получить список всех учеников
 * - Получить лучшего ученика по среднему балу
 * - Получить самый высокий средний бал
 *
 */

/**
 * Class ContactCore.
 */
class Student extends ObjectModel
{
    public $id;

    /** @var string Name */
    public $name;

    /** @var datetime */
    public $birthday;

    /** @var bool */
    public $isStudy = true;

    /** @var float */
    public $rate;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'student',
        'primary' => 'id_student',
        'multilang' => true,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255,
            ),
            'birthday' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDatetime',
            ),
            'is_study' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ),
            'rate' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ),
        ),
    );


    /**
    * Получить список всех учеников
    */
    public static function getListStudents()
    {
        return Db::getInstance()->executeS('
            SELECT *
            FROM ' . _DB_PREFIX_ . 'student
            ORDER BY `name`
        ');

    }

    /**
     * Получить лучшего ученика по среднему балу
     */
    public static function getBestStudent()
    {
        return Db::getInstance()->executeS('
            SELECT s.`name`
            FROM ' . _DB_PREFIX_ . 'student s
            ORDER BY s.`rate` desc
            LIMIT 1
        ');
    }

    /**
     * Получить самый высокий средний бал
     */
    public static function getBestRate()
    {
        $sql = new DbQuery();
        $sql->select('s.rate');
        $sql->from('student', 's');
        $sql->orderBy('s.rate');
        $sql->limit(1);
        return Db::getInstance()->executeS($sql);

    }

}
