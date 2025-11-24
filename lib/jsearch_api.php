<?php

/**
 * This file is a wrapper for our API calls.
 * Here, each endpoint needed will be exposes as a function.
 * The function will take the parameters needed for the API call and return the result.
 * The function will also handle the API key and endpoint.
 * Requires the api_helper.php file and load_api_keys.php file.
 */

/**
 * Fetches the stock quote for a given symbol.
 */

// rt524 11/22 uses the relevant input query which matches the rapidAPI required field
function fetch_jobs($query)
{
    $data = ["query" => $query, "page" => "1", "num_pages" => "1", "country" => "us"];
    $endpoint = "https://jsearch.p.rapidapi.com/search";
    $isRapidAPI = true;
    $rapidAPIHost = "jsearch.p.rapidapi.com";
    // $result = get($endpoint, "JSEARCH_API_KEY", $data, $isRapidAPI, $rapidAPIHost);

    // example of cached data to save quotes, comment out get() if using cached data for testing
    $result = ["status" => 200, "response" => <<<'API'
        {"status":"OK",
        "request_id":"0568e6ef-f7d7-41e3-86f1-f094a8707f6a",
        "parameters":
            {"query":"software engineer nj",
            "page":1,
            "num_pages":1,
            "date_posted":"all",
            "country":"us",
            "language":"en"},
            
        "data":[{
            "job_id":"5M6_eQwz6soyQ5t-AAAAAA==",
            "job_title":"Aerospace/Defense Manager Software Engineer with Active Secret Clearance- Camden,NJ",
            "employer_name":"Pacific Technical Resources",
            "employer_logo":null,
            "employer_website":"https://pacifictechnicalresources.com",
            "job_publisher":"Indeed",
            "job_employment_type":"Full-time",
            "job_employment_types":["FULLTIME"],
            "job_apply_link":"https://www.indeed.com/viewjob?jk=e86b54a543126315&utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic",
            "job_apply_is_direct":false,
            "apply_options":[{
                "publisher":"Indeed",
                "apply_link":"https://www.indeed.com/viewjob?jk=e86b54a543126315&utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic",
                "is_direct":false}],
            "job_description":"Manager, Software Engineering – Aerospace & Defense\n\nLocation: Camden, NJ\n\nSalary: $130K–$180K\n\nRelocation: Available\n\nClearance: Active DoD Secret or higher required\n\nAbout the Role\n\nJoin a rapidly growing team developing high-assurance encryption products critical to national defense. Lead software engineering for advanced network and tactical encryption systems while guiding a talented engineering team in a mission-driven environment.\n\nKey Responsibilities\n• Lead software design, development, and requirements analysis\n• Establish best practices, processes, and standards for the software team\n• Perform code and design reviews\n• Collaborate with Software, Electrical, and Systems Engineering leadership\n• Mentor and develop engineering talent; conduct performance reviews\n• Manage recruiting and hiring for the software team\n• Support proposals, cost estimation, BOEs, and EVMS as a Cost Account Manager\n• Contribute to hands-on software development as needed\n\nQualifications\n• Bachelor’s in Computer Science, Software, Computer, or Electrical Engineering (Master’s preferred)\n• 9+ years software development experience (or 7+ with a Master’s)\n• 2+ years leading or managing software teams\n• Active Secret clearance (minimum)\n• Experience hiring technical talent and contributing to technical/cost proposals\n• Experience with EVMS and CAM responsibilities\n\nPreferred Skills\n• Agile development experience\n• Proposal / BOE leadership\n• Real-time embedded or secure communications / encryption software experience\n• Info Assurance, IPSEC, Type 1 crypto, key management, or naval comms background\n• DevOps tools and processes",
            "job_is_remote":false,
            "job_posted_at":"1 day ago",
            "job_posted_at_timestamp":1763251200,
            "job_posted_at_datetime_utc":"2025-11-16T00:00:00.000Z",
            "job_location":"Camden, NJ",
            "job_city":"Camden",
            "job_state":"New Jersey",
            "job_country":"US",
            "job_latitude":39.9425241,
            "job_longitude":-75.1164689,
            "job_benefits":["health_insurance"],
            "job_google_link":"https://www.google.com/search?q=jobs&gl=us&hl=en&udm=8#vhid=vt%253D20/docid%253D5M6_eQwz6soyQ5t-AAAAAA%253D%253D&vssid=jobs-detail-viewer",
            "job_min_salary":120000,
            "job_max_salary":200000,
            "job_salary_period":"YEAR",
            "job_highlights":{
                "Qualifications":[
                    "Clearance: Active DoD Secret or higher required",
                    "9+ years software development experience (or 7+ with a Master’s)",
                    "2+ years leading or managing software teams",
                    "Active Secret clearance (minimum)",
                    "Experience hiring technical talent and contributing to technical/cost proposals",
                    "Experience with EVMS and CAM responsibilities",
                    "Secret (Required)"
                ],
                "Benefits":[
                    "Salary: $130K–$180K",
                    "Relocation: Available",
                    "Pay: $120,000.00 - 200,000.00 per year",
                    "401(k)","Health insurance","Relocation assistance"
                ],
                "Responsibilities":[
                    "Lead software engineering for advanced network and tactical encryption systems while guiding a talented engineering team in a mission-driven environment",
                    "Lead software design, development, and requirements analysis",
                    "Establish best practices, processes, and standards for the software team",
                    "Perform code and design reviews",
                    "Collaborate with Software, Electrical, and Systems Engineering leadership",
                    "Mentor and develop engineering talent; conduct performance reviews",
                    "Manage recruiting and hiring for the software team",
                    "Support proposals, cost estimation, BOEs, and EVMS as a Cost Account Manager",
                    "Contribute to hands-on software development as needed"
                ]
            },
            "job_onet_soc":"15113200",
            "job_onet_job_zone":"4"},
            {"job_id":"HyDn_scsWb8q1dzeAAAAAA==","job_title":"Lead Software Engineer, Back End","employer_name":"Capital One","employer_logo":null,"employer_website":"https://www.capitalone.com","job_publisher":"Women For Hire- Job Board","job_employment_type":"Full-time and Part-time","job_employment_types":["FULLTIME","PARTTIME"],"job_apply_link":"https://jobs.womenforhire.com/job/usa/paramus-nj/lead-software-engineer-back-end-594256/?utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic","job_apply_is_direct":false,"apply_options":[{"publisher":"Women For Hire- Job Board","apply_link":"https://jobs.womenforhire.com/job/usa/paramus-nj/lead-software-engineer-back-end-594256/?utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic","is_direct":false}],"job_description":"Lead Software Engineer, Back End\n\nDo you love building and pioneering in the technology space? …","job_is_remote":false,"job_posted_at":"20 hours ago","job_posted_at_timestamp":1763348400,"job_posted_at_datetime_utc":"2025-11-17T03:00:00.000Z","job_location":"Paramus, NJ","job_city":"Paramus","job_state":"New Jersey","job_country":"US","job_latitude":40.9482792,"job_longitude":-74.0672769,"job_benefits":["health_insurance"],"job_google_link":"https://www.google.com/search?q=jobs&gl=us&hl=en&udm=8#vhid=vt%3D20/docid%3DHyDn_scsWb8q1dzeAAAAAA%3D%3D&vssid=jobs-detail-viewer","job_salary":null,"job_min_salary":null,"job_max_salary":null,"job_salary_period":null,"job_highlights":{"Qualifications":["Bachelor’s Degree","At least 4 years of professional software engineering experience","At least 1 year experience with cloud computing"]},"job_onet_soc":"15113200","job_onet_job_zone":"4"},{"job_id":"khUcqbMXiFdPpH_QAAAAAA==","job_title":"SD-WAN (Versa) Engineer- W2 only","employer_name":"Jobs via Dice","employer_logo":"https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR9CC1NlnLA7sshF1s1dqKvk8U495jsMwImnyPP&s=0","employer_website":null,"job_publisher":"LinkedIn","job_employment_type":"Full-time","job_employment_types":["FULLTIME"],"job_apply_link":"https://www.linkedin.com/jobs/view/sd-wan-versa-engineer-w2-only-at-jobs-via-dice-4322779331?utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic","job_apply_is_direct":false,"apply_options":[{"publisher":"LinkedIn","apply_link":"https://www.linkedin.com/jobs/view/sd-wan-versa-engineer-w2-only-at-jobs-via-dice-4322779331?utm_campaign=google_jobs_apply&utm_source=google_jobs_apply&utm_medium=organic","is_direct":false}],"job_description":"Dice is the leading career destination …","job_is_remote":false,"job_posted_at":"4 hours ago","job_posted_at_timestamp":1763406000,"job_posted_at_datetime_utc":"2025-11-17T19:00:00.000Z","job_location":"Mt Laurel Township, NJ","job_city":"Mt Laurel Township","job_state":"New Jersey","job_country":"US","job_latitude":39.9241516,"job_longitude":-74.9499966,"job_benefits":null,"job_google_link":"https://www.google.com/search?q=jobs&gl=us&hl=en&udm=8#vhid=vt%253D20/docid%253DkhUcqbMXiFdPpH_QAAAAAA%253D%253D&vssid=jobs-detail-viewer","job_salary":null,"job_min_salary":null,"job_max_salary":null,"job_salary_period":null,"job_highlights":{"Qualifications":["Hands-on experience on Versa SD-WAN Solution","Experience with AWS Cloud & containerized platforms (Docker/Kubernetes)","Strong Networking fundamentals …"]},"job_onet_soc":"53601100","job_onet_job_zone":"1"}]}
        API
    ];

    error_log("API Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }

    // rt524 11/22 returns the relevant fields for the data
    // transform data
    $transformedResult = [];
    if (isset($result["data"])) {
        foreach ($result["data"] as $r) {
            $data = [
                "job_id"                   => $r["job_id"] ?? null,

                // location
                "country"                  => $r["job_country"] ?? null,
                "job_location"             => $r["job_location"] ?? null,
                "job_city"                 => $r["job_city"] ?? null,
                "job_state"                => $r["job_state"] ?? null,

                // basics
                "job_title"                => $r["job_title"] ?? null,
                "employer_name"            => $r["employer_name"] ?? null,
                "job_publisher"            => $r["job_publisher"] ?? null,
                "job_employment_type"      => $r["job_employment_type"] ?? null,
                "job_apply_link"           => $r["job_apply_link"] ?? null,

                // details
                "job_description"          => $r["job_description"] ?? null,
                "job_is_remote"            => $r["job_is_remote"] ?? null,
                "job_posted_at_datetime_utc" => $r["job_posted_at_datetime_utc"] ?? null,

                // highlights as arrays
                "qualifications"           => $r["job_highlights"]["Qualifications"] ?? [],
                "responsibilities"         => $r["job_highlights"]["Responsibilities"] ?? [],
                "benefits"                 => $r["job_highlights"]["Benefits"] ?? [],

                "is_api" => 1
            ];
            // append transformed result to data
           array_push($transformedResult, $data);
        }
    }
    return $transformedResult;
}