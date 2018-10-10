<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Mailer;

class Category extends Model {



	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", 
			array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()			
		));
		$this->setData($results[0]);
		Category::updatefile();
	}

	public function get($idcategory)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$idcategory
		]);
		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
		]);
		Category::updatefile();
	}

	public static function updateFile()
	{
		$categories = Category::listAll();
		$html = [];
		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));

	}

	public function getProducts($related = true)
	{
		$sql = new Sql();
		if ($related === true) {
			return $sql->select("
				select * from tb_products where idproduct in (
					select a.idproduct from tb_products a
					inner join tb_productscategories b on a.idproduct = b.idproduct
					where b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);
		}
		else {
			return $sql->select("
				select * from tb_products where idproduct not in (
					select a.idproduct from tb_products a
					inner join tb_productscategories b on a.idproduct = b.idproduct
					where b.idcategory = :idcategory
				);
			",[
				':idcategory'=>$this->getidcategory()
			]);
		}
	}

	public function getProductsPage($page = 1, $itemsPerPage = 8)
	{
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("select sql_calc_found_rows * from tb_products a
			inner join tb_productscategories b on a.idproduct = b.idproduct
			inner join tb_categories c on c.idcategory = b.idcategory
			where c.idcategory = :idcategory
			limit $start, $itemsPerPage;", [
				':idcategory'=>$this->getidcategory()
			]);
		$resultTotal = $sql->select("select found_rows() as nrtotal;");

		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}

	public function addProduct(Product $product)
	{
		$sql = new Sql();
		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}

	public function removeProduct(Product $product)
	{
		$sql = new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}
}

 ?>