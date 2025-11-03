@include('layouts.header')

<div class="container flex-grow-1">
    <div class="row g-4">

        {{-- LEFT: Analyzer --}}
        <div class="col-lg-8 col-md-12">
            <div class="card p-4">
                <h4 class="fw-bold text-primary mb-3">
                    <i class="fa-solid fa-magnifying-glass text-primary"></i> Analyze News / Tweets / Posts
                </h4>

                <form id="analyzeForm">
                    @csrf
                    <textarea name="content" id="content" rows="6" class="form-control mb-3" required
                        placeholder="Paste or type your content here..."></textarea>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-brain me-1"></i> Analyze with AI
                    </button>
                </form>

                <div id="loading" class="text-center mt-4" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Analyzing... please wait</p>
                </div>

                <div id="result" class="result-card p-4 mt-4 d-none">
                    <h5>
                        <i class="fa-regular fa-lightbulb me-2"></i><strong>Verdict:</strong>
                        <span id="verdictText" class="fw-bold"></span>
                    </h5>
                    <hr>
                    <h6><i class="fa-solid fa-gauge-high me-2"></i> AI Confidence:</h6>
                    <div class="progress my-2">
                        <div id="confidenceBar"
                            class="progress-bar bg-secondary progress-bar-striped progress-bar-animated"
                            style="width: 0%">0%</div>
                    </div>
                    <small id="confidenceLabel" class="text-muted fst-italic"></small>

                    <hr>
                    <h6><i class="fa-solid fa-comments me-2"></i> AI Explanation:</h6>
                    <p id="aiResponse" class="ai-explanation mt-2"></p>

                    <button class="btn btn-outline-primary btn-sm mt-2" id="shareBtn">
                        <i class="fa-solid fa-share-nodes"></i> Share Result
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT: Recent Checks --}}
        <div class="col-lg-4 col-md-12">
            <div class="card p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-rotate me-2"></i>Recent Checks</h5>
                <div class="recent-list">
                    @forelse($checks as $item)
                        <div class="recent-item" onclick="this.classList.toggle('expanded')">
                            <span class="text-content">{{ $item->content }}</span>
                            <span class="verdict-badge verdict-{{ str_replace(' ', '', $item->verdict ?? 'Unverified') }}">
                                {{ $item->verdict ?? 'Unverified' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">No checks yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')