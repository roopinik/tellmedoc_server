<?php
namespace App\Traits;
trait WizardQuerySteps {
    public string|null $currentStepQueryString = null;
    public string $queryStringStepName = 'step';

    public function queryStringWizardQueryString()
    {
        return [
            'currentStepQueryString' => ['as' => $this->queryStringStepName],
        ];
    }

    public function initializeWizardQueryString()
    {
        // Select the correct step from the URL Query String
        if ($step = collect($this->queryStringSteps())->search(request()->query($this->queryStringStepName))) {
            $this->currentStepName = $step;
        }
    }

    public function showStep($toStepName, array $currentStepState = [])
    {
        parent::showStep($toStepName, $currentStepState);

        // Update the URL Query String
        if ($parameter = $this->queryStringSteps()[$toStepName] ?? null) {
            $this->currentStepQueryString = $parameter;
        }
    }

    protected function queryStringSteps(): array
    {
        // To have "nice" URL parameters
        return [

        ];
    }
}
