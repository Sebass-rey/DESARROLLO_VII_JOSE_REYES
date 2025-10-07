<?php
// Archivo: clases.php

interface Inventariable {
    public function obtenerInformacionInventario();
}


class Producto implements Inventariable {
    public $id;
    public $nombre;
    public $descripcion;
    public $estado;
    public $stock;
    public $fechaIngreso;
    public $categoria;

    public function __construct($datos) {
        foreach ($datos as $clave => $valor) {
            if (property_exists($this, $clave)) {
                $this->$clave = $valor;
            }
        }
    }

    public function obtenerInformacionInventario() {
        return "ID: {$this->id}, Stock: {$this->stock}, Estado: {$this->estado}";
    }
}

class ProductoElectronico extends Producto {
    public $garantiaMeses;

    public function obtenerInformacionInventario() {
        return "Electrónico - Garantía: {$this->garantiaMeses} meses, Stock: {$this->stock}";
    }
}

class ProductoAlimento extends Producto {
    public $fechaVencimiento;

    public function obtenerInformacionInventario() {
        $vencimiento = $this->fechaVencimiento ?: 'No especificada';
        return "Alimento - Vence: {$vencimiento}, Stock: {$this->stock}";
    }
}

class ProductoRopa extends Producto {
    public $talla;

    public function obtenerInformacionInventario() {
        $talla = $this->talla ?: 'No especificada';
        return "Ropa - Talla: {$talla}, Stock: {$this->stock}";
    }
}

class GestorInventario {
    private $items = [];
    private $rutaArchivo = 'productos.json';

    public function obtenerTodos() {
        if (empty($this->items)) {
            $this->cargarDesdeArchivo();
        }
        return $this->items;
    }

    private function cargarDesdeArchivo() {
        if (!file_exists($this->rutaArchivo)) {
            return;
        }
        
        $jsonContenido = file_get_contents($this->rutaArchivo);
        $arrayDatos = json_decode($jsonContenido, true);
        
        if ($arrayDatos === null) {
            return;
        }
        
        foreach ($arrayDatos as $datos) {
            // Determinar qué tipo de producto crear basado en la categoría
            switch ($datos['categoria'] ?? '') {
                case 'electronico':
                    $this->items[] = new ProductoElectronico($datos);
                    break;
                case 'alimento':
                    $this->items[] = new ProductoAlimento($datos);
                    break;
                case 'ropa':
                    $this->items[] = new ProductoRopa($datos);
                    break;
                default:
                    $this->items[] = new Producto($datos);
            }
        }
    }

    private function persistirEnArchivo() {
        $arrayParaGuardar = array_map(function($item) {
            return get_object_vars($item);
        }, $this->items);
        
        file_put_contents(
            $this->rutaArchivo, 
            json_encode($arrayParaGuardar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function obtenerMaximoId() {
        if (empty($this->items)) {
            return 0;
        }
        
        $ids = array_map(function($item) {
            return $item->id;
        }, $this->items);
        
        return max($ids);
    }

    public function agregar($nuevoProducto) {
        if (!isset($nuevoProducto->id)) {
            $nuevoProducto->id = $this->obtenerMaximoId() + 1;
        }
        
        $this->items[] = $nuevoProducto;
        $this->persistirEnArchivo();
        return true;
    }

    //Metodo para eliminar un producto por su ID
    public function eliminar($idProducto) {
        $encontrado = false;
        $nuevosItems = [];
        
        foreach ($this->items as $producto) {
            if ($producto->id == $idProducto) {
                $encontrado = true;
            } else {
                $nuevosItems[] = $producto;
            }
        }
        
        if ($encontrado) {
            $this->items = array_values($nuevosItems);
            $this->persistirEnArchivo();
        }
        
        return $encontrado;
    }


    public function actualizar($productoActualizado) {
        foreach ($this->items as $indice => $producto) {
            if ($producto->id == $productoActualizado->id) {
                $this->items[$indice] = $productoActualizado;
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    public function cambiarEstado($idProducto, $estadoNuevo) {
        $producto = $this->obtenerPorId($idProducto);
        
        if (!$producto) {
            return false;
        }
        
        $producto->estado = $estadoNuevo;
        $this->persistirEnArchivo();
        return true;
    }

    public function filtrarPorEstado($estadoBuscado) {
        $items = $this->obtenerTodos();
        
        if (empty($estadoBuscado)) {
            return $items;
        }
        
        return array_filter($items, function($producto) use ($estadoBuscado) {
            return $producto->estado === $estadoBuscado;
        });
    }

    public function obtenerPorId($idBuscado) {
        $items = $this->obtenerTodos();
        
        foreach ($items as $producto) {
            if ($producto->id == $idBuscado) {
                return $producto;
            }
        }
        
        return null;
    }
}
?>