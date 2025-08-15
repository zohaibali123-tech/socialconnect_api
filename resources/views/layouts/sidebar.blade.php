<div class="d-flex flex-column flex-shrink-0 p-3 text-white" 
     style="width: 250px; height: 100vh; background: linear-gradient(180deg, #5257b6, #8489e5);">
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <button class="btn btn-warning w-100 mb-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createPostModal">
                + Create Post
            </button>
        </li>
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link text-white active">Home</a>
        </li>
        <li>
            <a href="{{ url('/posts') }}" class="nav-link text-white">See All Posts</a>
        </li>
        <li>
            <a href="{{ url('/my-posts') }}" class="nav-link text-white">My Posts</a>
        </li>
    </ul>
</div>
