<?php

/* common statements that do not belong to one place */

/**
 * @OA\Info(
 * 		title="ERP", 
 * 		version="0.1"
 * )
 */

/**
 * @OA\OpenApi(
 * 		x={
 * 			"tagGroups"={
 * 				{
 * 					"name"="Task Management",
 * 					"tags"={
 * 						"Task", "Subtask", "Assignement", "Comment", "Event", "Attachment"
 * 					}
 * 				}, 
 * 				{
 * 					"name"="File",
 * 					"tags"={
 * 						"File"
 * 					}
 * 				},
 * 				{
 * 					"name" = "Procedure Management",
 * 					"tags" = {
 * 						"Procedure", "Subprocedure", "Step", "Event (Procedure)", "Comment (Step)", "Attachment (Step)"
 * 					}
 * 				},
 * 				{
 * 					"name" = "GIS",
 * 					"tags" = {
 * 						"Stats",
 * 						"Feature", "Feature (Public)", 
 * 						"Search", "Search (Public)",
 * 						"POI", "POI (Public)",
 * 						"Custom Feature",
 * 						"Custom Feature (Public)",
 * 						"District", "District (Public)", 
 * 						"CSBM", "CSBM (Public)",
 * 						"Road", "Road (Public)",
 * 						"Parcel", "Parcel (Public)",
 * 						"Structure", "Structure (Public)", 
 * 						"Numbering", "Numbering (Public)", 
 * 						"Independent Section",
 * 						"Independent Section (Public)",
 * 						"Inhabitant", 
 * 						"Document",
 * 						"Tag (Structure)",
 * 						"Tag (Parcel)",
 * 						"Comment (Structure)",
 * 						"Comment (Parcel)",
 * 						"Attachment (Structure)",
 * 						"Procedure (Structure)",
 * 						"Procedure (Parcel)",
 * 						"Plan"
 * 					}
 * 				},
 *              {
 * 					"name" = "Sdm",
 * 					"tags" = {
 * 						"Auth (Grocer)",
 *                      "Forgotten Password (Grocer)",
 *                      "Reset Password (Grocer)",
 *                      "Creating First Password (Grocer)",
 *                      "App Transaction (Grocer)",
 *                      "App Profile (Grocer)",
 *                      "Grocer",
 *                      "Transaction",
 * 						
 * 					}
 * 				},
 * 				{
 * 					"name" = "Polling",
 * 					"tags" = {
 * 						"Auth",
 * 						"Queue", 
 * 						"DirectMessage",
 * 						"DemoMessage",
 * 						"Message",
 * 						"Citizen",
 * 						"MessageTemplate",
 * 						"FilterTemplate",
 * 						"Category (Citizen)",
 * 						"Tag (Citizen)",
 * 						"Survey",
 * 						"Submission",
 * 						"Submission (Public)",
 * 						"Filter",
 * 						"Tag Rule"
 * 					}
 * 				},
 * 			}
 * 		}
 * )
 */

/**
 * @OA\Server(
 * 		url="https://api.gungoren-dev.com/v1",
 * 		description="Pre Production Server"
 * )
 * 
 * @OA\Server(
 * 		url="https://api.gungoren.bel.tr/v1",
 * 		description="Production Server"
 * )
 * 
 * @OA\Server(
 * 		url="localhost:8080/v1",
 * 		description="Local Environment"
 * )
 */

/**
 * @OA\Schema(
 * 		schema="authorization",
 * 		type="string"
 * )
 */

/**
 * @OA\Parameter(
 * 		name="Authorization",
 * 		parameter="authorization",
 * 		in="header",
 * 		@OA\Schema(ref="#/components/schemas/authorization")
 * )
*/

/**
 * @OA\Schema(
 * 		schema="error",
 * 		type="object",
 * 		@OA\Property(
 * 			property="code",
 * 			type="integer"
 * 		),
 * 		@OA\Property(
 * 			property="detail",
 * 			type="string"
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="errors",
 * 		type="object",
 * 		@OA\Property(
 * 			property="errors",
 * 			type="array",
 * 			@OA\Items(
 * 				type="object",
 * 				ref="#/components/schemas/error"
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Response(
 * 		response="204",
 * 		description="No Content"
 * )
 */

/**
 * @OA\Response(
 * 		response="400",
 * 		description="BadRequest",
 *		@OA\JsonContent(ref="#/components/schemas/errors")
 * )
 */

/**
 * @OA\Response(
 * 		response="401",
 * 		description="Unatuhorized",
 *		@OA\JsonContent(ref="#/components/schemas/errors")
 * )
 */

/**
 * @OA\Response(
 * 		response="404",
 * 		description="NotFound",
 *		@OA\JsonContent(ref="#/components/schemas/errors")
 * )
 */


/**
 * @OA\Schema(
 * 		schema="personnel_basic",
 *     	type="object",
 *     	title="Personnel Basic",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="image_uri", 
 * 				type="string",
 *      	    format="uri",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="firstname", 
 * 				type="string"
 * 			),
 *      	@OA\Property(
 * 				property="lastname", 
 * 				type="string"
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="citizen",
 *     	type="object",
 *     	title="Citizen",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="tcno", 
 * 				type="string",
 * 				minLength=11,
 * 				maxLength=11
 * 			),
 * 			@OA\Property(
 * 				property="firstname", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="lastname", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="gender", 
 * 				type="integer",
 * 				nullable=true,
 * 				description="`1 = Female`, `2 = Male`"
 * 			),
 * 			@OA\Property(
 * 				property="address", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="phone", 
 * 				type="string",
 * 				nullable=true
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="corporation",
 *     	type="object",
 *     	title="Corporation",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="tax_number", 
 * 				type="string",
 * 				minLength=10,
 * 				maxLength=11
 * 			),
 * 			@OA\Property(
 * 				property="tax_office", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="title", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="address", 
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="phone", 
 * 				type="string",
 * 				nullable=true
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="feature_type",
 * 		type="string",
 * 		enum={"parcel", "structure", "numbering", "district", "road", "poi", "custom"}
 * )
 */

/**
 * @OA\Schema(
 * 		schema="department",
 *     	type="object",
 *     	title="Department",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="name", 
 * 				type="string"
 * 			),
 * 			@OA\Property(
 * 				property="parent_department",
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="director",
 *      	    type="string",
 *      	    nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="director_allowed_parent_depth",
 *      	    type="integer"
 * 			),
 * 			@OA\Property(
 * 				property="director_allowed_child_depth",
 *      	    type="integer"
 * 			),
 * 			@OA\Property(
 * 				property="member_allowed_parent_depth",
 *      	    type="integer"
 * 			),
 * 			@OA\Property(
 * 				property="member_allowed_child_depth",
 *      	    type="integer"
 * 			)
 * 		),
 * 
 * 		@OA\Property(
 * 			property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="director",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="object",
 * 					ref="#/components/schemas/personnel_basic"
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Parameter(
 * 		parameter="comment_include",
 * 		name="include",
 * 		in="query",
 * 		@OA\Schema(
 * 			type="string",
 * 			enum={
 * 				"commentator"
 * 			}
 * 		)
 * 	)
 */

/**
 * @OA\Schema(
 * 		schema="comment_request_body",
 * 		title="Comment",
 * 		type="object",
 * 		required={"message"},
 * 		@OA\Property(
 * 			property="message",
 * 			type="string"
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="department_basic",
 *     	type="object",
 *     	title="Department",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="name", 
 * 				type="string"
 * 			)
 * 		)
 * )
 */

 
/**
 * @OA\Schema(
 * 		schema="task",
 *     	type="object",
 *     	title="Task",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="assigner", 
 * 				type="integer",
 * 				minimum=1
 * 			),
 * 			@OA\Property(
 * 				property="title", 
 * 				type="string"
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="description", 
 * 				type="string", 
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="start_date", 
 * 				type="string", 
 * 				format="date-time", 
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="due_date", 
 * 				type="string", 
 * 				format="date-time", 
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="location", 
 * 				type="object", 
 * 				nullable=true,
 * 				@OA\Property(
 * 					property="latitude", 
 * 					type="string", 
 * 					minimum="-90", 
 * 					maximum="90"
 * 				),
 * 				@OA\Property(
 * 					property="longitude", 
 * 					type="string", 
 * 					minimum="-180", 
 * 					maximum="180"
 * 				)
 * 			),
 * 			@OA\Property(
 * 				property="priority",
 * 				type="integer",
 * 				minimum=1,
 * 				maximum=5,
 * 				description="`1 = Clear`, `2 = Low`, `3 = Medium`, `4 = High`, `5 = Urgent`"
 * 			),
 * 			@OA\Property(
 * 				property="status",
 * 				type="integer",
 * 				minimum=1,
 * 				maximum=5,
 * 				description="`1 = Open`, `2 = InProgress`, `3 = Delayed`, `4 = Complete`, `5 = Cancelled`"
 * 			),
 * 			@OA\Property(
 * 				property="created_on", 
 * 				type="string", 
 * 				format="date-time"
 * 			),
 * 			@OA\Property(
 * 				property="edited_on", 
 * 				type="string", 
 * 				format="date-time"
 * 			)
 * 		),
 * 
 * 		@OA\Property(
 * 			property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="subtask",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/subtask"
 * 					)	
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="assignee",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/personnel_basic"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="comment",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/comment"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="event",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/event"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="attachment",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/attachment"
 * 					)
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="subtask",
 *     	type="object",
 *     	title="Subtask",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="assigner", 
 * 				type="integer",
 * 				minimum=1
 * 			),
 * 			@OA\Property(
 * 				property="title", 
 * 				type="string"
 * 			),
 * 			@OA\Property(
 * 				property="description", 
 * 				type="string", 
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="due_date", 
 * 				type="string", 
 * 				format="date-time", 
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="location", 
 * 				type="object", 
 * 				nullable=true,
 * 				@OA\Property(
 * 					property="latitude", 
 * 					type="string", 
 * 					minimum="-90", 
 * 					maximum="90"
 * 				),
 * 				@OA\Property(
 * 					property="longitude", 
 * 					type="string", 
 * 					minimum="-180", 
 * 					maximum="180"
 * 				)
 * 			),
 * 			@OA\Property(
 * 				property="priority",
 * 				type="integer",
 * 				minimum=1,
 * 				maximum=5,
 * 				description="`1 = Clear`, `2 = Low`, `3 = Medium`, `4 = High`, `5 = Urgent`"
 * 			),
 * 			@OA\Property(
 * 				property="status",
 * 				type="integer",
 * 				minimum=1,
 * 				maximum=5,
 * 				description="`1 = Open`, `2 = InProgress`, `3 = Delayed`, `4 = Complete`, `5 = Cancelled`"
 * 			),
 * 			@OA\Property(
 * 				property="created_on", 
 * 				type="string", 
 * 				format="date-time"
 * 			)
 * 		),
 * 
 * 		@OA\Property(
 * 			property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="assignee",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/personnel_basic"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="comment",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/comment"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="event",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/event"
 * 					)
 * 				)
 * 			),
 * 	
 * 			@OA\Property(
 * 				property="attachment",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="array",
 * 					@OA\Items(
 * 						ref="#/components/schemas/attachment"
 * 					)
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="comment",
 * 		type="object",
 * 		title="Comment",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="message",
 * 				type="string"
 * 			),
 * 			@OA\Property(
 * 				property="edited_on",
 * 				type="string",
 * 				format="date-time"
 * 			),
 * 			@OA\Property(
 * 				property="commented_on",
 * 				type="string",
 * 				format="date-time"
 * 			)
 * 		),
 * 
 * 		@OA\Property(
 * 		property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="commentator",
 * 				type="object",
 * 				@OA\Property(
 * 					property="data",
 * 					type="object",
 * 					ref="#/components/schemas/personnel_basic"
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="event",
 * 		type="object",
 * 		title="Event",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="type",
 * 				type="integer",
 * 				minimum=1,
 * 				maximum=14,
 * 				description="`StatusChanged = 1`, `TitleChanged = 2`, `Assigned = 3`, `Deassigned = 4`, `DescriptionUpdated = 5`, `DueDateChanged = 6`, `SubtaskCreated = 7`, `SubtaskRemoved = 8`, `PriorityChanged = 10`, `LocationChanged = 11`, `AttachmentAttached = 12`, `AttachmentRemoved = 13`, `StartDateChanged = 14`"
 * 			),
 * 			@OA\Property(
 * 				property="occurred_on",
 * 				type="string",
 * 				format="date-time"
 * 			),
 * 			@OA\Property(
 * 				property="data",
 * 				type="object",
 * 				description="
 * 					Depending on the event type, this object will contain following properties: 
 * 					`type == 1` => `{ status_old: int, status_new: int }`,
 * 					`type == 2` => `{ title_old: string, title_new: string }`,
 * 					`type == 3` => `{ assignee: int }`,
 * 					`type == 4` => `{ assignee: int }`,
 * 					`type == 5` => `{ description: string }`,
 * 					`type == 6` => `{ due_date_old: string(datetime), due_date_new: string(datetime) }`,
 * 					`type == 7` => `{ title: string }`,
 * 					`type == 8` => `{ title: string }`,
 * 					`type == 10` => `{ priority_old: int, priority_new: int }`,
 * 					`type == 11` => `{ location: { latitude: string, longitude: string } }`,
 * 					`type == 12` => `{ name: string }`,
 * 					`type == 13` => `{ name: string }`,
 * 					`type == 14` => `{ start_date_old: string(datetime), start_date_new: string(datetime) }`
 * 				"
 * 			)
 * 		),
 * 		
 * 		@OA\Property(
 * 		property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="enabler",
 * 				type="object",
 * 				nullable=true,
 * 				description="NULL if event was triggered by the system",
 * 				@OA\Property(
 * 					property="data",
 * 					type="object",
 * 					ref="#/components/schemas/personnel_basic"
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="attachment",
 * 		type="object",
 * 		title="attachment",
 * 
 * 		@OA\Property(
 * 			property="id",
 * 			type="string"
 * 		),
 * 		@OA\Property(
 * 			property="type",
 * 			type="string"
 * 		),
 * 		
 * 		@OA\Property(
 * 			type="object",
 * 			@OA\Property(
 * 				property="name",
 * 				type="string"
 * 			),
 * 			@OA\Property(
 * 				property="extension",
 * 				type="string",
 * 				nullable=true
 * 			),
 * 			@OA\Property(
 * 				property="uri",
 * 				type="string",
 * 				format="uri",
 * 				description="Direct access URI"
 * 			)
 * 		),
 * 
 * 		@OA\Property(
 * 		property="relationships",
 * 			type="object",
 * 	
 * 			@OA\Property(
 * 				property="uploader",
 * 				type="object",
 * 				nullable="true",
 * 				description="NULL if attachment was added by the system",
 * 				@OA\Property(
 * 					property="data",
 * 					type="object",
 * 					ref="#/components/schemas/personnel_basic"
 * 				)
 * 			)
 * 		)
 * )
 */

/**
 * @OA\Schema(
 * 		schema="procedure_type",
 * 		type="integer",
 * 		description="
 * 			`(Numarataj) Numbering = 1`
 *			`(Yapı Ruhsatı) BuildingPermit = 2`
 *			`(İnşaat İstikamet Rölevesi) ConstructionDirectionSurveying = 3`
 *			`(Kot Kesit Rölevesi) ElevationProfileSurveying = 4`
 *			`(Yola Terk) Expropriation = 5`
 *			`(İfraz) Parcelling = 6`
 *			`(Tevhit) Amalgamation = 7`
 *			`(Ecrimisil) OccupationCompensationByManagement = 8`
 *			`(Ecrimisil - Vatandaş) OccupationCompensationByCitizen = 9`
 *			`(1/5000 Ölçekli Plan Değişimi) PlanChange1to5000Internal = 10`
 *			`(1/5000 Ölçekli Plan Değişimi - Vatandaş) PlanChange1to5000External = 11`
 *			`(Yıkım Ruhsatı) DeconstructionPermit = 12`
 *			`(Avan Proje) PreliminaryDesign = 13`
 *			`(Yazılı İmar Durumu) WrittenZoningStatus = 14`
 *			`Riskli Yapı = 15`
 *			`Kira Yardımı (Mal Sahibi) = 16`
 *			`Kira Yardımı (Kiracı) = 17`
 *			`Test = 18`"
 * )
 */

?>