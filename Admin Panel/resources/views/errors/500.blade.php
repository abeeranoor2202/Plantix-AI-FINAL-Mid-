<x-error-page
    code="500"
    title="Something went wrong"
    message="An unexpected error occurred on our server."
    action-text="Return home"
    :action-href="url('/')"
    accent="#f59e0b"
    :support-text="'Our team has been notified. Reference: ' . now()->timestamp"
/>
