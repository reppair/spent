<?php

namespace App\Livewire;

use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Models\Group;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class CreateCategory extends Component
{
    public int $groupId;

    /**
     * @var Collection<\App\Models\Group>
     */
    public Collection $groups;

    public CategoryForm $categoryForm;

    public function mount(): void
    {
        $this->categoryForm->group_id = $this->groupId;
    }

    public function createCategory(): void
    {
        // Since group_id can be changed via form input, we need to authorize the request
        $this->authorize('createCategory', [Group::class, $this->categoryForm->group_id]);

        $category = Category::create($this->categoryForm->validate());

        $this->reset('categoryForm.name');

        Flux::modal('create-category')->close();

        $this->dispatch('category-created', categoryId: $category->id);
    }

    public function render(): View
    {
        return view('livewire.create-category');
    }
}
