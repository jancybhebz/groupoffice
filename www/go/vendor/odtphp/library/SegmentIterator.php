<?php
/**
 * Segments iterator
 * You need PHP 5.2 at least
 * You need Zip Extension or PclZip library
 * Encoding : ISO-8859-1
 * Last commit by $Author: neveldo $
 * Date - $Date: 2009-06-17 11:11:57 +0200 (mer., 17 juin 2009) $
 * SVN Revision - $Rev: 42 $
 * Id : $Id: SegmentIterator.php 42 2009-06-17 09:11:57Z neveldo $
 *
 * @copyright  GPL License 2008 - Julien Pauli - Cyril PIERRE de GEYER - Anaska (http://www.anaska.com)
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version 1.3
 */
class SegmentIterator implements RecursiveIterator
{
    private $ref;
    private $key;
    public function __construct(array $ref)
    {
        $this->ref = $ref;
        $this->key = 0;
        $this->keys = array_keys($this->ref);
    }

		#[\ReturnTypeWillChange]
    public function hasChildren()
    {
        return $this->valid() && $this->current() instanceof Segment;
    }
	#[\ReturnTypeWillChange]
    public function current()
    {
        return $this->ref[$this->keys[$this->key]];
    }

	#[\ReturnTypeWillChange]
    function getChildren()
    {
        return new self($this->current()->children);
    }
	#[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }
	#[\ReturnTypeWillChange]
    public function valid()
    {
        return array_key_exists($this->key, $this->keys);
    }
	#[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->key = 0;
    }

	#[\ReturnTypeWillChange]
    public function next()
    {
        $this->key ++;
    }
}

?>
