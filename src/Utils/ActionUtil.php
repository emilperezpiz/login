<?php

namespace App\Utils;

use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;

class ActionUtil
{
    public $defaultTheme = "default";
    public $defaultLanguage = "pt";
    public $defaultPaymentDay = 15;

    public function crearCarpeta($ruta)
    {
        $fs = new Filesystem;

        if (!$fs->exists($ruta)) {
            $fs->mkdir($ruta);
        }
    }

    public function uploadImage($parameters, $entity, $root, $x, $y, $width, $height)
    {
        if (NULL === $parameters['img']) {
            return;
        }

        // create an image manager instance with favored driver
        //$manager = new ImageManager(array('driver' => 'imagick'));
        $manager = new ImageManager();
        // to finally create image instances
        $img = $manager->make($parameters['img']->getRealPath());
        $img->orientate();

        $nombreFoto = $parameters['img']->getClientOriginalName();
        $extensionFoto = $parameters['img']->getClientOriginalExtension();
        $nombre = uniqid() . ".$extensionFoto";
        $current = $entity->getUsername() . "/profile";
        $directorioDestino = "$root/$current";
        $img->crop($width, $height, $x, $y)
            ->resize(150, 150, function ($constraint) {
                $constraint->aspectRatio();
            })->save($directorioDestino . '/' . $nombre);
        $dir = "$root/$current";
        $entity->setPath("$dir/$nombre");
    }

    // dimensiones del thumbnail
    private $tumbWidth = 360;
    private $tumbHeight = 222;

    public function uploadProduct($image, $entity, $root, $uniqid)
    {
        if (NULL === $image) {
            return;
        }

        // create an image manager instance with favored driver
        //$manager = new ImageManager(array('driver' => 'imagick'));
        $manager = new ImageManager();
        // to finally create image instances
        $img = $manager->make($image->getRealPath());
        $img->orientate();
        $nombreFoto = $image->getClientOriginalName();
        $extension = $image->getClientOriginalExtension();
        // nombre con el que se guardara la imgen
        $name = uniqid() . ".$extension";
        // directorio donde se almacenara la imagen original
        $directorioDestino = $root . $uniqid;
        $img->save($directorioDestino . '/' . $name);
        $dir = $root . $uniqid;
        // creo un objeto de tipo imagen
        $image = new \App\Document\ProductImage();
        // guardo la ruta donde se guardo la imagen original
        $image->setPath("$dir/$name");
        // Creando el thumbnail de la imagen complementaria
        $img->resize($this->tumbWidth, $this->tumbHeight, function ($constraint) {
            $constraint->aspectRatio();
        })->save($directorioDestino . '/thumb/' . $name);
        $dirThumb = $directorioDestino . "/thumb";
        // guardo la ruta donde se guardo la imagen original
        $image->setThumb("$dirThumb/$name");
        $image->setProduct($entity);

        return $image;
    }

    public function uploadImg($image, $entity, $root, $uniqid, $thumb = true)
    {
        if (NULL === $image) {
            return;
        }

        // create an image manager instance with favored driver
        //$manager = new ImageManager(array('driver' => 'imagick'));
        $manager = new ImageManager();
        // to finally create image instances
        $img = $manager->make($image->getRealPath());
        $img->orientate();
        $width = $img->width();
        $height = $img->height();
        $nombreFoto = $image->getClientOriginalName();
        $extension = $image->getClientOriginalExtension();
        // nombre con el que se guardara la imgen
        $name = uniqid() . ".$extension";
        // directorio donde se almacenara la imagen original
        $directorioDestino = $root . $uniqid;
        $img->save($directorioDestino . '/' . $name);
        $dir = $root . $uniqid;
        // guardo la ruta donde se guardo la imagen original
        $entity->setPath("$dir/$name");

        if ($thumb) {
            // Creando el thumbnail de la imagen complementaria
            $img->resize($this->tumbWidth, $this->tumbHeight, function ($constraint) {
                $constraint->aspectRatio();
            })->save($directorioDestino . '/thumb/' . $name);
            $dirThumb = $directorioDestino . "/thumb";
            $entity->setThumb("$dirThumb/$name");
            $image->setProduct($entity);
        }

        return $image;
    }
}
