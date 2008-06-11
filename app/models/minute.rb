class Minute < ActiveRecord::Base
  belongs_to :event
  belongs_to :author, :foreign_key => "author_id", :class_name => "Person"

  validates_presence_of :event_id, :author_id, :body, :created_at  
  validates_length_of   :body, :maximum => 10240, :message => "too long. Max length is 10KB"
end
