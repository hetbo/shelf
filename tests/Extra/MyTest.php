<?php

it('can open library home page', function () {
    $this->get(route('shelf.index'))->assertStatus(200)->assertSee('Hello');
});