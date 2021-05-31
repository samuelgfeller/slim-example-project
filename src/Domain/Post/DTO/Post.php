<?php

namespace App\Domain\Post\DTO;


use App\Common\ArrayReader;
use App\Domain\User\DTO\User;

class Post
{
    public ?int $id;
    public ?int $userId;
    public ?string $message;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;
    public ?User $user;

    /**
     * Post constructor.
     * @param array|null $postData
     */
    public function __construct(array $postData = null) {
        $arrayReader = new ArrayReader($postData);
        $this->id = $arrayReader->findInt('id');
        $this->userId = $arrayReader->findInt('user_id');
        $this->message = $arrayReader->findString('message');
        $this->createdAt = $arrayReader->findString('created_at');
        $this->updatedAt = $arrayReader->findString('updated_at');
        $this->deletedAt = $arrayReader->findString('deleted_at');
    }

    /**
     * Returns all values of object as array.
     * The array keys should match with the database
     * column names since it is likely used to
     * modify a database table
     *
     * @return array
     */
    public function toArray(): array
    {
        // Not include required, from db non nullable values if they are null -> for update
        if($this->id !== null){ $post['id'] = $this->id;}
        if($this->userId !== null){ $post['user_id'] = $this->userId;}

        // Message is nullable and null is a valid value so it has to be included todo detect null values and add IS for cakequery builder IS NULL
        $post['message'] = $this->message;

        return $post;
    }
}